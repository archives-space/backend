<?php

namespace App\Provider\User;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\User\UserRepository;
use App\Utils\Response\ErrorCodes;
use App\Utils\User\UserArrayGenerator;

class UserProvider
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
     * @param UserRepository     $userRepository
     * @param UserArrayGenerator $userArrayGenerator
     */
    public function __construct(
        UserRepository $userRepository,
        UserArrayGenerator $userArrayGenerator
    )
    {
        $this->userRepository     = $userRepository;
        $this->userArrayGenerator = $userArrayGenerator;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function getUserById(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_USER));
        }

        return (new ApiResponse($this->userArrayGenerator->userToArray($user)));
    }

    /**
     * @return ApiResponse
     */
    public function getUsers()
    {
        $users = array_map(function (User $user) {
            return $this->userArrayGenerator->userToArray($user);
        }, $this->userRepository->getAllUsers()->toArray());

        return (new ApiResponse($users));
    }
}