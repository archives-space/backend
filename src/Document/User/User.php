<?php

namespace App\Document\User;

use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Odm\Document(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    const ROLE_ADMIN       = 'ROLE_ADMIN';
    const ROLE_CONTRIBUTOR = 'ROLE_CONTRIBUTOR';
    const ROLE_MODERATOR   = 'ROLE_MODERATOR';
    const ROLE_USER        = 'ROLE_USER';

    /**
     * @Odm\Id
     */
    private $id;

    /**
     * @Odm\Field(type="string")
     * @Odm\UniqueIndex()
     */
    private $username;

    /**
     * @Odm\Field(type="collection")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @Odm\Field(type="string")
     */
    private $password;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Odm\UniqueIndex()
     */
    private $email;

    /**
     * @var integer
     * @Odm\Field(type="bool")
     */
    private $isLocked;

    /**
     * @var integer
     * @Odm\Field(type="bool")
     */
    private $isVerified;

    /**
     * @var integer
     * @Odm\Field(type="bool")
     */
    private $isDeleted;

    /**
     * @var integer
     * @Odm\Field(type="integer")
     */
    private $score;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $lastLoginAt;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $createAt;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $updatedAt;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $deletedAt;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $publicName;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $location;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $biography;

    public function __construct()
    {
        $this->isLocked   = false;
        $this->isVerified = false;
        $this->isDeleted  = false;
        $this->score      = 0;
        $this->createAt   = new \DateTime("NOW");
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsLocked(): int
    {
        return $this->isLocked;
    }

    /**
     * @param int $isLocked
     * @return User
     */
    public function setIsLocked(int $isLocked): User
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsVerified(): int
    {
        return $this->isVerified;
    }

    /**
     * @param int $isVerified
     * @return User
     */
    public function setIsVerified(int $isVerified): User
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsDeleted(): int
    {
        return $this->isDeleted;
    }

    /**
     * @param int $isDeleted
     * @return User
     */
    public function setIsDeleted(int $isDeleted): User
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return User
     */
    public function setScore(int $score): User
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLoginAt(): ?\DateTime
    {
        return $this->lastLoginAt;
    }

    /**
     * @param \DateTime|null $lastLoginAt
     * @return User
     */
    public function setLastLoginAt(?\DateTime $lastLoginAt): User
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreateAt(): ?\DateTime
    {
        return $this->createAt;
    }

    /**
     * @param \DateTime|null $createAt
     * @return User
     */
    public function setCreateAt(?\DateTime $createAt): User
    {
        $this->createAt = $createAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return User
     */
    public function setUpdatedAt(?\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime|null $deletedAt
     * @return User
     */
    public function setDeletedAt(?\DateTime $deletedAt): User
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPublicName(): ?string
    {
        return $this->publicName;
    }

    /**
     * @param string|null $publicName
     * @return User
     */
    public function setPublicName(?string $publicName): User
    {
        $this->publicName = $publicName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     * @return User
     */
    public function setLocation(?string $location): User
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBiography(): ?string
    {
        return $this->biography;
    }

    /**
     * @param string|null $biography
     * @return User
     */
    public function setBiography(?string $biography): User
    {
        $this->biography = $biography;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
