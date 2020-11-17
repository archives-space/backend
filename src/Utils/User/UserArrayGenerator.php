<?php

namespace App\Utils\User;

use App\Document\User\User;
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
    public function toArray(User $user): array
    {
        return [
            'id'          => $user->getId(),
            'username'    => $user->getUsername(),
            'email'       => $user->getEmail(),
            'islocked'    => $user->getIsLocked(),
            'isverified'  => $user->getIsVerified(),
            'isdeleted'   => $user->getIsDeleted(),
            'score'       => $user->getScore(),
            'lastloginat' => $user->getLastLoginAt(),
            'createat'    => $user->getCreateAt(),
            'updatedat'   => $user->getUpdatedAt(),
            'deletedat'   => $user->getDeletedAt(),
            'publicname'  => $user->getPublicName(),
            'location'    => $user->getLocation(),
            'biography'   => $user->getBiography(),
            'role'        => $user->getRoles(),
            'userDetail'  => $this->router->generate('USER_DETAIL', [
                'id' => $user->getId(),
            ]),
        ];
    }
}