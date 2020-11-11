<?php

namespace App\Document;

use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Odm\Document(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
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
        return (string) $this->username;
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
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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
