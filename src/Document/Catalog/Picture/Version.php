<?php

namespace App\Document\Catalog\Picture;

use App\Document\User\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use App\Repository\Catalog\Picture\VersionRepository;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\Document(repositoryClass=VersionRepository::class)
 */
class Version
{
    /**
     * @Odm\Id(strategy="INCREMENT")
     */
    private $id;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $name;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $description;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $source;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $takenAt;

    /**
     * @var Exif|null
     * @Odm\EmbedOne(targetDocument=Exif::class)
     */
    private $exif;

    /**
     * @var Position|null
     * @Odm\EmbedOne(targetDocument=Position::class)
     */
    private $position;

    /**
     * @var Place|null
     * @Odm\ReferenceOne(targetDocument=Place::class)
     */
    private $place;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @var User
     * @Odm\ReferenceOne(targetDocument=User::class)
     */
    private $createdBy;

    /**
     * @var ObjectChange[]
     * @Odm\EmbedMany(targetDocument=ObjectChange::class)
     */
    private $objectChanges;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime('NOW'));
        $this->users         = [];
        $this->objectChanges = [];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Version
     */
    public function setName(string $name): Version
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Version
     */
    public function setDescription(?string $description): Version
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     * @return Version
     */
    public function setSource(?string $source): Version
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTakenAt(): ?\DateTime
    {
        return $this->takenAt;
    }

    /**
     * @param \DateTime|null $takenAt
     * @return Version
     */
    public function setTakenAt(?\DateTime $takenAt): Version
    {
        $this->takenAt = $takenAt;
        return $this;
    }

    /**
     * @return Exif|null
     */
    public function getExif(): ?Exif
    {
        return $this->exif;
    }

    /**
     * @param Exif $exif
     * @return Version
     */
    public function setExif(Exif $exif): Version
    {
        $this->exif = $exif;
        return $this;
    }

    /**
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return Version
     */
    public function setPosition(Position $position): Version
    {
        $this->position = $position;
        return $this;
    }


    /**
     * @return Place|null
     */
    public function getPlace(): ?Place
    {
        return $this->place;
    }

    /**
     * @param Place|null $place
     * @return Version
     */
    public function setPlace(?Place $place): Version
    {
        $this->place = $place;
        return $this;
    }

    /**
     * @param User $user
     * @return Version
     */
    public function addUser(User $user): Version
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return Version
     */
    public function setCreatedAt(?\DateTime $createdAt): Version
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * @param User|null $createdBy
     * @return Version
     */
    public function setCreatedBy(?User $createdBy): Version
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getObjectChanges(): PersistentCollection
    {
        return $this->objectChanges;
    }

    /**
     * @param ObjectChange[] $objectChanges
     * @return Version
     */
    public function setObjectChanges(array $objectChanges): Version
    {
        $this->objectChanges = $objectChanges;
        return $this;
    }

    /**
     * @param ObjectChange $objectChange
     * @return Version
     */
    public function addObjectChange(ObjectChange $objectChange): Version
    {
        $this->objectChanges[] = $objectChange;
        return $this;
    }

}