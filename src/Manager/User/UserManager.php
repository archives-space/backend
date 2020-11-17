<?php

namespace App\Manager\User;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Repository\User\UserRepository;
use App\Manager\BaseManager;
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
 * @package App\Manager\User
 */
class UserManager extends BaseManager
{
    const BODY_PARAM_USERNAME   = "username";
    const BODY_PARAM_PASSWORD   = "password";
    const BODY_PARAM_EMAIL      = "email";
    const BODY_PARAM_PUBLICNAME = "publicName";
    const BODY_PARAM_LOCATION   = "location";
    const BODY_PARAM_BIOGRAPHY  = "biography";
    const BODY_PARAM_ROLES      = "roles";

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Zxcvbn
     */
    private $zxcvbn;

    /**
     * @var UserArrayGenerator
     */
    private $userArrayGenerator;

    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        UserArrayGenerator $userArrayGenerator
    )
    {
        parent::__construct($dm, $requestStack);

        $this->userRepository     = $userRepository;
        $this->passwordEncoder    = $passwordEncoder;
        $this->zxcvbn             = new Zxcvbn();
        $this->userArrayGenerator = $userArrayGenerator;
    }


    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $this->checkMissedField();
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if ($user = $this->userRepository->getUserByUsername($this->body[self::BODY_PARAM_USERNAME])) {
            $this->apiResponse->addError(new Error(ErrorCodes::USERNAME_EXIST));
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

        $this->apiResponse->setData($this->userArrayGenerator->toArray($user));

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
            $this->apiResponse->addError(new Error(ErrorCodes::NO_USER));
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $username = $this->body[self::BODY_PARAM_USERNAME] ?? $user->getUsername();
        // Si on change de username mais qu'il existe deja dans la db alors on throw une exception
        if ($user->getUsername() !== $username && $this->userRepository->getUserByUsername($username)) {
            $this->apiResponse->addError(new Error(ErrorCodes::USERNAME_EXIST));
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

        $this->apiResponse->setData($this->userArrayGenerator->toArray($user));

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
            $this->apiResponse->addError(new Error(ErrorCodes::NO_USER));
            return $this->apiResponse;
        }

        $this->setPassword($user);

        $this->apiResponse->setData($this->userArrayGenerator->toArray($user));

        return $this->apiResponse;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function delete(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_USER));
        }

        $this->dm->remove($user);
        $this->dm->flush();

        return (new ApiResponse([]));
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
            $this->apiResponse->addError(new Error(ErrorCodes::EMAIL_EXIST));
            return;
        }

        if (!EmailCheck::isValid($email)) {
            $this->apiResponse->addError(new Error(ErrorCodes::EMAIL_NOT_VALID));
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
            $this->apiResponse->addError(new Error(ErrorCodes::PASSWORD_WEAK));
            return;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
    }

    /**
     * @return string[]
     */
    public function requiredField()
    {
        return [
            self::BODY_PARAM_USERNAME,
            self::BODY_PARAM_PASSWORD,
            self::BODY_PARAM_EMAIL,
        ];
    }
}