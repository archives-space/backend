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

class UserProvider extends BaseProvider
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * UserProvider constructor.
     * @param RequestStack       $requestStack
     * @param UserRepository     $userRepository
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
     */
    public function findById(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(Errors::USER_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->apiResponse->setData($this->userTransformer->toArray($user))->setNbTotalData(1);
        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function findAll()
    {
        $data = $this->userRepository->getAllUsersPaginate($this->nbPerPage, $this->page);

        $users = array_map(function (User $user) {
            return $this->userTransformer->toArray($user);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($users)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}