<?php

namespace App\Provider\User;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Model\ApiResponse\Error;
use App\Provider\BaseProvider;
use App\Repository\User\UserRepository;
use App\Utils\Response\ErrorCodes;
use App\ArrayGenerator\User\UserArrayGenerator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class UserProvider extends BaseProvider
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserArrayGenerator
     */
    private $userArrayGenerator;

    /**
     * UserProvider constructor.
     * @param RequestStack       $requestStack
     * @param UserRepository     $userRepository
     * @param UserArrayGenerator $userArrayGenerator
     */
    public function __construct(
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserArrayGenerator $userArrayGenerator
    )
    {
        parent::__construct($requestStack);
        $this->userRepository     = $userRepository;
        $this->userArrayGenerator = $userArrayGenerator;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            $this->apiResponse->addError(new Error(ErrorCodes::USER_NOT_FOUND));
            return $this->apiResponse;
        }

        $this->apiResponse->setData($this->userArrayGenerator->toArray($user))->setNbTotalData(1);
        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function findAll()
    {
        $data  = $this->userRepository->getAllUsersPaginate($this->nbPerPage, $this->page);
        $users = array_map(function (User $user) {
            return $this->userArrayGenerator->toArray($user);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($users)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}