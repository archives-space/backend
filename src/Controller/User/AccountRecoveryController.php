<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\DataTransformer\User\UserTransformer;
use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Repository\User\UserRepository;
use App\Utils\Response\Errors;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use function PHPUnit\Framework\isInstanceOf;

/**
 * Class UserController
 * @package App\Controller\User
 * @Route(defaults={"_format"="json"})
 */
class AccountRecoveryController extends AbstractController
{
    /**
     * @var ResetPasswordHelperInterface
     */
    private ResetPasswordHelperInterface $resetPasswordHelper;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var UserTransformer
     */
    private UserTransformer $userTransformer;

    /**
     * @var JWTTokenManagerInterface
     */
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        UserRepository $userRepository,
        RequestStack $requestStack,
        MailerInterface $mailer,
        UserTransformer $userTransformer,
        ContainerInterface $container,
        JWTTokenManagerInterface $JWTManager
    )
    {
        parent::__construct($requestStack);
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->mailer = $mailer;
        $this->JWTManager = $JWTManager;
        $this->userTransformer = $userTransformer;
    }

    /**
     * Ask for a account recovery
     * @Route("/recovery/ask", name="RECOVERY_ASK", methods={"POST"})
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function askRecovery(): Response
    {
        $apiResponse = new ApiResponse();
        $body = $this->getParsedBody();
        if (!isset($body['login'])) {
            $apiResponse->addError(
                Error::extend(Errors::QUERY_MISSING_FIELD, "'login' field missing")
            );
            return $apiResponse->getResponse();
        }

        // first, get the user by username or by email
        $user = $this->userRepository->getUserByUsernameOrEmail($body['login']);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $apiResponse->getResponse();
        }

        try {
            $token = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $apiResponse->addError(
                Error::extend(Errors::UNKNOWN_ERROR, $e->getReason())
            );
            return $apiResponse->getResponse();
        }

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@wikiarchives.space', 'WikiArchives.space'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('account_recovery/email.html.twig')
            ->context([
                'link' => $this->getParameter('web_base_url') . '/recovery/execute?token=' . $token->getToken(),
                'token' => $token,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;

        $this->mailer->send($email);

        return $apiResponse->getResponse();
    }

    /**
     * Execute a recovery, will return a JWT
     * @Route("/recovery/execute", name="RECOVERY_EXECUTE", methods={"POST"})
     * @return Response
     * @throws ExceptionInterface
     */
    public function executeRecovery(): Response
    {
        $apiResponse = new ApiResponse();
        $body = $this->getParsedBody();
        if (!isset($body['token'])) {
            $apiResponse->addError(
                Error::extend(Errors::QUERY_MISSING_FIELD, "'token' field missing")
            );
            return $apiResponse->getResponse();
        }
        $token = $body['token'];
        /** @var User $user */
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            if ($e instanceof InvalidResetPasswordTokenException) {
                $apiResponse->addError(Errors::RECOVERY_INVALID_TOKEN);
                return $apiResponse->getResponse();
            }
            if ($e instanceof ExpiredResetPasswordTokenException) {
                $apiResponse->addError(Errors::RECOVERY_EXPIRED_TOKEN);
                return $apiResponse->getResponse();
            }
            $apiResponse->addError(
                Error::extend(
                    Errors::UNKNOWN_ERROR,
                    sprintf("Recovering account failed - %s", $e->getReason())
                )
            );
            return $apiResponse->getResponse();
        }
        $apiResponse->setData([
            'user' => $this->userTransformer->toArray($user),
            // When a user is created we also want to create a token for immediate login
            'token' => $this->JWTManager->create($user)
        ]);

        $this->resetPasswordHelper->removeResetRequest($token);

        return $apiResponse->getResponse();
    }
}
