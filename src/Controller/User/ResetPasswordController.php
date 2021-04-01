<?php

namespace App\Controller\User;

use App\Document\User\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\User\UserRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use ZxcvbnPhp\Zxcvbn;

/**
 * @Route("/reset-password",defaults={"_format"="json"})
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var DocumentManager
     */
    private DocumentManager $dm;

    /**
     * ResetPasswordController constructor.
     * @param ResetPasswordHelperInterface $resetPasswordHelper
     * @param UserRepository               $userRepository
     * @param DocumentManager              $dm
     */
    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        UserRepository $userRepository,
        DocumentManager $dm
    )
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository      = $userRepository;
        $this->dm                  = $dm;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request",methods={"POST"})
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
//        $form = $this->createForm(ResetPasswordRequestFormType::class);
//        $form->handleRequest($request);
        return $this->processSendingPasswordResetEmail(
            json_decode($request->getContent(), true)['email'] ?? null,
            $mailer
        );
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password",methods={"POST"})
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): JsonResponse
    {
//        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return (new ApiResponse())->addError(Errors::USER_RESET_PASSWORD_VALIDATION_FAILED)->getResponse();
        }

        if (!$password = json_decode($request->getContent(), true)['plainPassword'] ?? null) {
            return (new ApiResponse())->addError('Password needed')->getResponse();
        }

        if ((new Zxcvbn())->passwordStrength($password)['score'] <= 1) {
            return (new ApiResponse())->addError(Errors::USER_PASSWORD_WEAK)->getResponse();
        }

        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode the plain password, and set it.
        $encodedPassword = $passwordEncoder->encodePassword(
            $user,
            $password
        );

        $user->setPassword($encodedPassword);
        $this->dm->flush();

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();

        return $this->json([
            'message' => 'Password reseted',
        ]);
    }

    private function processSendingPasswordResetEmail(?string $emailFormData, MailerInterface $mailer): JsonResponse
    {
        $user = $this->userRepository->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return (new ApiResponse())->addError(Errors::USER_NOT_FOUND)->getResponse();
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return (new ApiResponse())->addError(Errors::USER_RESET_PASSWORD_FAILED)->getResponse();
        }

        $tokenValue = $resetToken->getToken();

        $email = (new TemplatedEmail())
            ->from(new Address('email@free.fr', 'Wiki Archives password reset'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->json([
            'message' => 'Password reset email send',
            'token'   => $tokenValue,
        ]);
    }
}
