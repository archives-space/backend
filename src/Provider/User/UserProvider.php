<?php

namespace App\Provider\User;

use App\DataTransformer\User\UserTransformer;
use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\User\UserRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserProvider extends BaseProvider
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var UserTransformer
     */
    private UserTransformer $userTransformer;

    /**
     * UserProvider constructor.
     * @param RequestStack $requestStack
     * @param UserRepository $userRepository
     * @param UserTransformer $userTransformer
     */
    public function __construct(
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserTransformer $userTransformer
    )
    {
        parent::__construct($requestStack);
        $this->userRepository     = $userRepository;
        $this->userTransformer = $userTransformer;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws ExceptionInterface
     */
    public function findById(string $id): ApiResponse
    {
        if (!$user = $this->userRepository->getUserByIdOrUsername($id)) {
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }
        $this->apiResponse->setData($this->userTransformer->toArray($user))->setNbTotalData(1);

        return $this->apiResponse;
    }

    /**
     * @param string $idOrUsername
     * @return ApiResponse
     * @throws ExceptionInterface
     */
    public function findByIdOrUsername(string $idOrUsername): ApiResponse
    {
        if (!$user = $this->userRepository->getUserByIdOrUsername($idOrUsername)) {
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }
        $this->apiResponse->setData($this->userTransformer->toArray($user))->setNbTotalData(1);

        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException|ExceptionInterface
     */
    public function findAll(): ApiResponse
    {
        $data = $this->userRepository->getAllUsersPaginate($this->nbPerPage, $this->page);

        $users = array_map(function (User $user) {
            return $this->userTransformer->toArray($user);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($users)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}