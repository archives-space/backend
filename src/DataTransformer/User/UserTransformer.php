<?php

namespace App\DataTransformer\User;

use App\DataTransformer\BaseDataTransformer;
use App\Document\User\User;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserTransformer extends BaseDataTransformer
{

    /**
     * @param $object
     * @return mixed
     */
    public function toArray($object, bool $fullInfo = true)
    {
        $user = $this->normalize($object);

        $user['detail'] = $this->router->generate('USER_DETAIL', [
            'id' => $object->getId(),
        ]);

        return $user;
    }

    /**
     * @param $array
     * @return User
     * @throws ExceptionInterface
     */
    public function toObject($array): User
    {
        return $this->denormalize($array, User::class);
    }
}