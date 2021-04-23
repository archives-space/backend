<?php

namespace App\Manager\User;

use App\DataTransformer\User\UserTransformer;
use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\User\UserRepository;
use App\Manager\BaseManager;
use App\Utils\FileManager;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
    private UserRepository $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var UserTransformer
     */
    private UserTransformer $userTransformer;

    /**
     * @var User
     */
    private User $postedUser;

    /**
     * @var JWTManager
     */
    private JWTManager $JWTManager;

    /**
     * @var FileManager
     */
    private FileManager $fileManager;

    /**
     * UserManager constructor.
     * @param DocumentManager $dm
     * @param RequestStack $requestStack
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     * @param UserTransformer $userTransformer
     * @param ContainerInterface $container
     * @param FileManager $fileManager
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator,
        UserTransformer $userTransformer,
        ContainerInterface $container,
        FileManager $fileManager
    )
    {
        parent::__construct($dm, $requestStack, $validator);

        $this->userRepository  = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->userTransformer = $userTransformer;
        $this->JWTManager = $container->get('lexik_jwt_authentication.jwt_manager');
        $this->fileManager = $fileManager;
    }

    public function setPostedObject()
    {
        $this->postedUser = $this->userTransformer->toObject($this->body);
    }


    /**
     * @return ApiResponse
     * @throws MongoDBException
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function create(): ApiResponse
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

        $this->apiResponse->setData([
            'user' => $this->userTransformer->toArray($user),
            // When a user is created we also want to create a token for immediate login
            'token' => $this->JWTManager->create($user)
        ]);

        return $this->apiResponse;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws MongoDBException
     * @throws Exception|ExceptionInterface
     */
    public function edit(string $id): ApiResponse
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
     * @throws Exception|ExceptionInterface
     */
    public function editPassword(string $id): ApiResponse
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
    public function delete(string $id): ApiResponse
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->dm->remove($user);
        $this->dm->flush();

        return $this->apiResponse;
    }

    /**
     * @param User $user
     * @return null
     * @throws Exception
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

    /**
     * @param string $id
     * @return ApiResponse
     * @throws ExceptionInterface
     * @throws MongoDBException
     */
    public function editAvatar(string $id): ApiResponse
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }

        $uploadedFile = $this->requestStack->getMasterRequest()->files->get('avatar');
        if ($uploadedFile == null) {
            $this->apiResponse->addError(Errors::QUERY_MISSING_FIELD);
            return $this->apiResponse;
        }

        $file = $this->fileManager->parse($uploadedFile);

        if (!$file->isImage()) {
            $this->apiResponse->addError(Errors::PICTURE_INVALID_MIME_TYPE);
            return $this->apiResponse;
        }

        if ($user->getAvatar() !== null && ($file->getHash() === $user->getAvatar()->getHash())) {
            $this->apiResponse->setData($this->userTransformer->toArray($user));

            return $this->apiResponse;
        }
        // TODO: add file type validation

        if ($user->getAvatar() !== null) {
            $this->fileManager->remove($user->getAvatar());
        }
        $this->fileManager->upload($uploadedFile, $file);
        $user->setAvatar($file);

        $this->dm->flush();

        $this->apiResponse->setData($this->userTransformer->toArray($user));

        return $this->apiResponse;
    }

}