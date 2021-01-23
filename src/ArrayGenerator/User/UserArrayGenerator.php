<?php

namespace App\ArrayGenerator\User;

use App\ArrayGenerator\BaseArrayGenerator;
use App\Document\User\User;
use Symfony\Component\Routing\RouterInterface;

class UserArrayGenerator extends BaseArrayGenerator
{

    public function __construct(RouterInterface $router)
    {
        parent::__construct($router);
    }

    /**
     * @param User $object
     * @param bool $fullInfo
     * @return array
     */
    public function toArray($object, bool $fullInfo = false): array
    {
        $user = $this->serialize($object);

        $user['detail'] = $this->router->generate('USER_DETAIL', [
            'id' => $object->getId(),
        ]);

        return $user;

        return [
            'id'          => $object->getId(),
            'username'    => $object->getUsername(),
            'email'       => $object->getEmail(),
            'isLocked'    => $object->getIsLocked(),
            'isVerified'  => $object->getIsVerified(),
            'isDeleted'   => $object->getIsDeleted(),
            'score'       => $object->getScore(),
            'lastLoginAt' => $object->getLastLoginAt(),
            'createdAt'   => $object->getCreatedAt(),
            'updatedAt'   => $object->getUpdatedAt(),
            'deletedAt'   => $object->getDeletedAt(),
            'publicName'  => $object->getPublicName(),
            'location'    => $object->getLocation(),
            'biography'   => $object->getBiography(),
            'role'        => $object->getRoles(),
            'detail'      => $this->router->generate('USER_DETAIL', [
                'id' => $object->getId(),
            ]),
        ];
    }
}