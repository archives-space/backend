<?php

namespace App\Manager\User;

use App\DataTransformer\User\UserTransformer;
use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\User\UserRepository;
use App\Manager\BaseManager;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserManager
 * @package App\Manager\User
 */
class UserManager extends BaseManager
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * @var User
     */
    private $postedUser;

    /**
     * UserManager constructor.
     * @param DocumentManager              $dm
     * @param RequestStack                 $requestStack
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface           $validator
     * @param UserTransformer              $userTransformer
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        UserTransformer $userTransformer
    )
    {
        parent::__construct($dm, $requestStack, $validator);

        $this->userRepository  = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->userTransformer = $userTransformer;
    }

    public function setPostedObject()
    {
        $this->postedUser = $this->userTransformer->toObject($this->body);
    }


    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $user = $this->postedUser;

        $this->setPassword($user);
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->validateDocument($user, ['create']);
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


        if($this->postedUser->getUsername() && $user->getUsername() !== $this->postedUser->getUsername()){
            $this->validateDocument($this->postedUser, ['username']);
        }
        if($this->postedUser->getEmail() && $user->getEmail() !== $this->postedUser->getEmail()){
            $this->validateDocument($this->postedUser, ['email']);
        }

        $user->setUsername($this->postedUser->getUsername() ?? $user->getUsername());
        $user->setPublicName($this->postedUser->getPublicName() ?? $user->getPublicName());
        $user->setLocation($this->postedUser->getLocation() ?? $user->getLocation());
        $user->setBiography($this->postedUser->getBiography() ?? $user->getBiography());
        $user->setRoles($this->postedUser->getRoles() ?? $user->getRoles());
        $user->setEmail($this->postedUser->getEmail() ?? $user->getEmail());

        $this->setPassword($user);


        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->validateDocument($user, ['edit']);
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
    private function setPassword(User $user)
    {
        if (!$this->postedUser->getPassword()) {
            return null;
        }

        $user->setPassword($this->postedUser->getPassword() ?: $user->getPassword());

        $this->validateDocument($user, ['password']);

        if ($this->apiResponse->isError()) {
            return null;
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

        return null;
    }
}