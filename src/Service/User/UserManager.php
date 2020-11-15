<?php

namespace App\Service\User;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Repository\UserRepository;
use App\Utils\Response\ErrorCodes;
use App\Utils\User\UserArrayGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use voku\helper\EmailCheck;
use ZxcvbnPhp\Zxcvbn;

/**
 * Class UserManager
 * @package App\Service\User
 */
class UserManager
{
    const BODY_PARAM_USERNAME   = "username";
    const BODY_PARAM_PASSWORD   = "password";
    const BODY_PARAM_EMAIL      = "email";
    const BODY_PARAM_PUBLICNAME = "publicName";
    const BODY_PARAM_LOCATION   = "location";
    const BODY_PARAM_BIOGRAPHY  = "biography";
    const BODY_PARAM_ROLES      = "roles";

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @var Zxcvbn
     */
    private $zxcvbn;

    /**
     * @var ApiResponse
     */
    private $apiResponse;

    /**
     * @var UserArrayGenerator
     */
    private $userArrayGenerator;

    /**
     * UserManager constructor.
     * @param DocumentManager              $dm
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param RequestStack                 $requestStack
     * @param UserArrayGenerator           $userArrayGenerator
     */
    public function __construct(
        DocumentManager $dm,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        RequestStack $requestStack,
        UserArrayGenerator $userArrayGenerator
    )
    {
        $this->dm                 = $dm;
        $this->userRepository     = $userRepository;
        $this->passwordEncoder    = $passwordEncoder;
        $this->requestStack       = $requestStack;
        $this->zxcvbn             = new Zxcvbn();
        $this->userArrayGenerator = $userArrayGenerator;
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->body        = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        $this->apiResponse = new ApiResponse();
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function checkMissedField()
    {
        $missedFields = $this->missedFields();
        if (count($missedFields) > 0) {
            $this->apiResponse->addError(new Error(ErrorCodes::MISSING_FIELD, sprintf('This fields are missing : "%s"', implode(', ', $missedFields))));
        }
        return $this;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        /*if ($user = $this->userRepository->getUserByUsernameOrEmail($this->body[self::BODY_PARAM_USERNAME], $this->body[self::BODY_PARAM_EMAIL])) {
            $this->apiResponse
                ->addError('Ce username ou email existe déja')
            ;
        }*/

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if ($user = $this->userRepository->getUserByUsername($this->body[self::BODY_PARAM_USERNAME])) {
            $this->apiResponse->addError(new Error(ErrorCodes::USERNAME_EXIST, 'Username already taken'));
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $user = new User();
        $user->setUsername($this->body[self::BODY_PARAM_USERNAME]);
        $user->setPublicName($this->body[self::BODY_PARAM_PUBLICNAME] ?? null);
        $user->setLocation($this->body[self::BODY_PARAM_LOCATION] ?? null);
        $user->setBiography($this->body[self::BODY_PARAM_BIOGRAPHY] ?? null);

        $this->setRoles($user);
        $this->setEmail($user);
        $this->setPassword($user);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($user);
        $this->dm->flush();

        $this->apiResponse->setData($this->userArrayGenerator->userToArray($user));

        return $this->apiResponse;

    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function edit(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(new Error(ErrorCodes::NO_USER, 'User not found'));
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $username = $this->body[self::BODY_PARAM_USERNAME] ?? $user->getUsername();
        // Si on change de username mais qu'il existe deja dans la db alors on throw une exception
        if ($user->getUsername() !== $username && $this->userRepository->getUserByUsername($username)) {
            $this->apiResponse->addError(new Error(ErrorCodes::USERNAME_EXIST, 'Username already taken'));
        }
        $user->setUsername($username);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $user->setPublicName($this->body[self::BODY_PARAM_PUBLICNAME] ?? $user->getPublicName());
        $user->setLocation($this->body[self::BODY_PARAM_LOCATION] ?? $user->getLocation());
        $user->setBiography($this->body[self::BODY_PARAM_BIOGRAPHY] ?? $user->getBiography());

        $this->setRoles($user);
        $this->setEmail($user);
        $this->setPassword($user);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->flush();

        $this->apiResponse->setData($this->userArrayGenerator->userToArray($user));

        return $this->apiResponse;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws \Exception
     */
    public function editPassword(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(new Error(ErrorCodes::NO_USER, 'User not found'));
            return $this->apiResponse;
        }

        $this->setPassword($user);

        $this->apiResponse->setData($this->userArrayGenerator->userToArray($user));

        return $this->apiResponse;
    }

    /**
     * @param User $user
     * @return null
     * @throws \Exception
     */
    private function setEmail(User $user)
    {
        if (!$email = $this->body[self::BODY_PARAM_EMAIL] ?? null) {
            return;
        }

        // ici on fournis l'email mais il est identique donc on fais rien
        if ($email === $user->getEmail()) {
            return;
        }

        if ($this->userRepository->getUserByEmail($email)) {
            $this->apiResponse->addError(new Error(ErrorCodes::EMAIL_EXIST, 'Email already taken'));
            return;
        }

        if (!EmailCheck::isValid($email)) {
            $this->apiResponse->addError(new Error(ErrorCodes::EMAIL_NOT_VALID, 'Email not valid'));
            return;
        }

        $user->setEmail($email);
    }

    /**
     * @param User $user
     */
    private function setRoles(User $user)
    {
        if (!$roles = $this->body[self::BODY_PARAM_ROLES] ?? null) {
            return;
        }

        $user->setRoles(is_array($roles) ? $roles : [$roles]);
    }

    /**
     * @param User $user
     * @return null
     * @throws \Exception
     */
    private function setPassword(User $user)
    {
        if (!$password = $this->body[self::BODY_PARAM_PASSWORD] ?? null) {
            return;
        }

        if ($this->zxcvbn->passwordStrength($password)['score'] <= 1) {
            $this->apiResponse->addError(new Error(ErrorCodes::PASSWORD_WEAK, 'Password weak'));
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
    }

    /**
     * @return string[]
     */
    private function missedFields()
    {
        if (!$this->body) {
            return $this->requiredField();
        }
        $missingKeys = array_diff_key(array_flip($this->requiredField()), $this->body);
        return array_intersect_key($this->requiredField(),
            array_flip($missingKeys)
        );
    }

    /**
     * @return string[]
     */
    private function requiredField()
    {
        return [
            self::BODY_PARAM_USERNAME,
            self::BODY_PARAM_PASSWORD,
            self::BODY_PARAM_EMAIL,
        ];
    }

    /**
     * @return string[]
     */
    private function field()
    {
        return [
            self::BODY_PARAM_USERNAME,
            self::BODY_PARAM_PASSWORD,
            self::BODY_PARAM_EMAIL,
            self::BODY_PARAM_PUBLICNAME,
            self::BODY_PARAM_LOCATION,
            self::BODY_PARAM_BIOGRAPHY,
            self::BODY_PARAM_ROLES,
        ];
    }
}