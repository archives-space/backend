<?php

namespace App\Document\User;

use App\Repository\User\ResetPasswordRequestRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

/**
 * @Odm\Document(repositoryClass=ResetPasswordRequestRepository::class)
 * @Odm\HasLifecycleCallbacks()
 */
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * @Odm\Id
     */
    private $id;

    /**
     * @ReferenceOne(targetDocument=User::class)
     */
    private $user;

    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): object
    {
        return $this->user;
    }
}
