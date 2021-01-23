<?php

namespace App\Manager\User;

use App\DataTransformer\User\UserTransformer;
use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Repository\User\UserRepository;
use App\Manager\BaseManager;
use App\Utils\Response\Errors;
use App\ArrayGenerator\User\UserArrayGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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

    /**
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * UserManager constructor.
     * @param DocumentManager              $dm
     * @param RequestStack                 $requestStack
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserArrayGenerator           $userArrayGenerator
     * @param ValidatorInterface           $validator
     * @param UserTransformer              $userTransformer
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        UserArrayGenerator $userArrayGenerator,
        ValidatorInterface $validator,
        UserTransformer $userTransformer
    )
    {
        parent::__construct($dm, $requestStack, $validator);

        $this->userRepository     = $userRepository;
        $this->passwordEncoder    = $passwordEncoder;
        $this->zxcvbn             = new Zxcvbn();
        $this->userArrayGenerator = $userArrayGenerator;
        $this->userTransformer    = $userTransformer;
    }

    public function setFields()
    {
        $this->username   = $this->body[self::BODY_PARAM_USERNAME] ?? null;
        $this->password   = $this->body[self::BODY_PARAM_PASSWORD] ?? null;
        $this->email      = $this->body[self::BODY_PARAM_EMAIL] ?? null;
        $this->publicName = $this->body[self::BODY_PARAM_PUBLICNAME] ?? null;
        $this->location   = $this->body[self::BODY_PARAM_LOCATION] ?? null;
        $this->biography  = $this->body[self::BODY_PARAM_BIOGRAPHY] ?? null;
        $this->roles      = $this->body[self::BODY_PARAM_ROLES] ?? null;
    }


    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $user = $this->userTransformer->toObject($this->body);
        $this->validateDocument($user);
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if ($user = $this->userRepository->getUserByUsername($this->username)) {
            $this->apiResponse->addError(Errors::USER_USERNAME_EXIST);
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->setEmail($user);
        $this->setPassword($user);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($user);
        $this->dm->flush();

        $this->apiResponse->setData($this->userTransformer->toArray($user));

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
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $userUpdated = $this->userTransformer->toObject($this->body);

        $username = $userUpdated->getUsername() ?? $user->getUsername();
        // Si on change de username mais qu'il existe deja dans la db alors on throw une exception
        if ($user->getUsername() !== $username && $this->userRepository->getUserByUsername($username)) {
            $this->apiResponse->addError(Errors::USER_USERNAME_EXIST);
        }
        $user->setUsername($username);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $user->setPublicName($userUpdated->getPublicName() ?? $user->getPublicName());
        $user->setLocation($userUpdated->getLocation() ?? $user->getLocation());
        $user->setBiography($userUpdated->getBiography() ?? $user->getBiography());
        $user->setRoles($userUpdated->getRoles() ?? $user->getRoles());

//        $this->setRoles($user);
        $this->setEmail($user);
        $this->setPassword($user);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->flush();

        $this->apiResponse->setData($this->userTransformer->toArray($user));

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
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->setPassword($user);

        $this->apiResponse->setData($this->userTransformer->toArray($user));

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
            return (new ApiResponse(null, Errors::USER_NOT_FOUND));
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
        if (!$email = $this->email ?? null) {
            return;
        }

        // ici on fournis l'email mais il est identique donc on fais rien
        if ($email === $user->getEmail()) {
            return;
        }

        if ($this->userRepository->getUserByEmail($email)) {
            $this->apiResponse->addError(Errors::USER_EMAIL_EXIST);
            return;
        }

        if (!EmailCheck::isValid($email)) {
            $this->apiResponse->addError(Errors::USER_EMAIL_NOT_VALID);
            return;
        }

        $user->setEmail($email);
    }

    /**
     * @param User $user
     */
    private function setRoles(User $user)
    {
        if (!$roles = $this->roles ?? null) {
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
        if (!$password = $this->password ?? null) {
            return;
        }

        if ($this->zxcvbn->passwordStrength($password)['score'] <= 1) {
            $this->apiResponse->addError(new Error(Errors::USER_PASSWORD_WEAK));
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