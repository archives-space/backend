<?php

namespace App\DataTransformer\User;

use App\DataTransformer\BaseDataTransformer;
use App\Document\User\User;
use App\Utils\FileManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserTransformer extends BaseDataTransformer
{

    /**
     * @var FileManager
     */
    private FileManager $fileManager;

    public function __construct(RouterInterface $router, FileManager $fileManager)
    {
        parent::__construct($router);
        $this->fileManager = $fileManager;
    }

    /**
     * @param $object
     * @param bool $fullInfo
     * @return mixed
     * @throws ExceptionInterface
     */
    public function toArray($object, bool $fullInfo = true)
    {
        /** @var User $object */
        $user = $this->normalize($object);

        $user['detail'] = $this->router->generate('USER_DETAIL', [
            'id' => $object->getId(),
        ]);

        if ($object->getAvatar() !== null) {
            $user['avatar']['url'] = $this->fileManager->generateUrl($object->getAvatar());
        }

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