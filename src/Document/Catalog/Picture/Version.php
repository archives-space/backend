<?php

namespace App\Document\Catalog\Picture;

use App\Document\Catalog\Picture;
use App\Document\User\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var string
     * @Odm\Id(strategy="INCREMENT")
     */
    private string $id;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private string $name;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private ?string $description;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private ?string $source;

    /**
     * @var DateTime|null
     * @Odm\Field(type="date")
     */
    private ?DateTime $takenAt;

    /**
     * @var Exif|null
     * @Odm\EmbedOne(targetDocument=Exif::class)
     */
    private ?Exif $exif;

    /**
     * @var Position|null
     * @Odm\EmbedOne(targetDocument=Position::class)
     */
    private ?Position $position;

    /**
     * @var Place|null
     * @Odm\ReferenceOne(targetDocument=Place::class)
     */
    private ?Place $place;

    /**
     * @var Position|null
     * @Odm\EmbedOne(targetDocument=License::class)
     * @Assert\Valid
     */
    private $license;

    /**
     * @var DateTime
     * @Odm\Field(type="date")
     */
    private DateTime $createdAt;

    /**
     * @var User
     * @Odm\ReferenceOne(targetDocument=User::class)
     */
    private User $createdBy;

    /**
     * @var ObjectChange[]
     * @Odm\EmbedMany(targetDocument=ObjectChange::class)
     */
    private $objectChanges;

    /**
     * @var User[]
     * @Odm\ReferenceMany (targetDocument=User::class)
     */
    private $makers;

    /**
     * @var Picture
     * @Odm\ReferenceOne(targetDocument=Picture::class)
     */
    private Picture $picture;
    
    public function __construct()
    {
        $this->setCreatedAt(new DateTime('NOW'));
        $this->objectChanges = new ArrayCollection();
        $this->makers = new ArrayCollection();
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
     * @return DateTime|null
     */
    public function getTakenAt(): ?DateTime
    {
        return $this->takenAt;
    }

    /**
     * @param DateTime|null $takenAt
     * @return Version
     */
    public function setTakenAt(?DateTime $takenAt): Version
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
     * @return License|null
     */
    public function getLicense(): ?License
    {
        return $this->license;
    }

    /**
     * @param License|null $license
     * @return Picture
     */
    public function setLicense(?License $license): Picture
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @param User $maker
     * @return Version
     */
    public function addMaker(User $maker): Version
    {
        $this->makers[] = $maker;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     * @return Version
     */
    public function setCreatedAt(?DateTime $createdAt): Version
    {
        $this->createdAt = $createdAt;
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
    public function addObjectChange(ObjectChange $objectChange): self
    {
        $this->objectChanges[] = $objectChange;
        return $this;
    }

    /**
     * @return Picture
     */
    public function getPicture(): Picture
    {
        return $this->picture;
    }

    /**
     * @param Picture $picture
     * @return Version
     */
    public function setPicture(Picture $picture): self
    {
        $this->picture = $picture;
        return $this;
    }


}
