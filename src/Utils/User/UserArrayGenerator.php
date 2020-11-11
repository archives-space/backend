<?php

namespace App\Utils\User;

use App\Document\User;
use Symfony\Component\Routing\RouterInterface;

class UserArrayGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * UserArrayGenerator constructor.
     * @param RouterInterface $router
     */
    public function __construct(
        RouterInterface $router
    )
    {
        $this->router = $router;
    }

    /**
     * @param User $user
     * @return array
     */
    public function userToArray(User $user): array
    {
        return [
            'id'         => $user->getId(),
            'username'   => $user->getUsername(),
            'role'       => $user->getRoles(),
            'userDetail' => $this->router->generate('USER_DETAIL', [
                'id' => $user->getId(),
            ]),
        ];
    }
}