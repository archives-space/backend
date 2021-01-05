<?php

namespace App\ArrayGenerator\User;

use App\ArrayGenerator\BaseArrayGenerator;
use App\Document\User\User;

class UserArrayGenerator extends BaseArrayGenerator
{
    /**
     * @param User $object
     * @param bool $fullinfo
     * @return array
     */
    public function toArray($object, bool $fullinfo = false): array
    {
        return [
            'id'          => $object->getId(),
            'username'    => $object->getUsername(),
            'email'       => $object->getEmail(),
            'islocked'    => $object->getIsLocked(),
            'isverified'  => $object->getIsVerified(),
            'isdeleted'   => $object->getIsDeleted(),
            'score'       => $object->getScore(),
            'lastloginat' => $object->getLastLoginAt(),
            'createdat'   => $object->getCreatedAt(),
            'updatedat'   => $object->getUpdatedAt(),
            'deletedat'   => $object->getDeletedAt(),
            'publicname'  => $object->getPublicName(),
            'location'    => $object->getLocation(),
            'biography'   => $object->getBiography(),
            'role'        => $object->getRoles(),
            'detail'      => $this->router->generate('USER_DETAIL', [
                'id' => $object->getId(),
            ]),
        ];
    }
}