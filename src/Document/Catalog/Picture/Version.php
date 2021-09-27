<?php

namespace App\Document\Catalog\Picture;


use App\Document\Catalog\Picture;
use App\Document\User\User;
use App\Traits\Document\Catalog\Picture\ExifTrait;
use App\Traits\Document\Catalog\Picture\LicenseTrait;
use App\Traits\Document\Catalog\Picture\ObjectChangeTrait;
use App\Traits\Document\Catalog\Picture\PlaceTrait;
use App\Traits\Document\Catalog\Picture\PositionTrait;
use App\Traits\Document\Catalog\Picture\ResolutionTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use App\Repository\Catalog\Picture\VersionRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\Document(repositoryClass=VersionRepository::class)
 */
class Version
{
    use ExifTrait;
    use LicenseTrait;
    use ObjectChangeTrait;
    use PlaceTrait;
    use PositionTrait;
    use ResolutionTrait;

    /**
     * @Odm\Id
     */
    private $id;

    /**
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $name;

    /**
     * @Odm\Field(type="string")
     */
    private $description;

    /**
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $source;

    /**
     * @Odm\Field(type="date")
     */
    private $takenAt;


    /**
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @Odm\ReferenceOne(targetDocument=User::class)
     */
    private $createdBy;

    /**
     * @Odm\ReferenceMany (targetDocument=User::class)
     */
    private $makers;

    /**
     * @Odm\ReferenceOne(targetDocument=Picture::class)
     */
    private $picture;


    public function __construct()
    {
        $this->setCreatedAt(new \DateTime('NOW'));
        $this->objectChanges = new ArrayCollection();
        $this->makers        = new ArrayCollection();
        $this->resolutions   = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
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
    public function setName(string $name): self
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
    public function setDescription(?string $description): self
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
    public function setSource(?string $source): self
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
    public function setTakenAt(?\DateTime $takenAt): self
    {
        $this->takenAt = $takenAt;
        return $this;
    }

    /**
     * @param User $maker
     * @return Version
     */
    public function addMaker(User $maker): self
    {
        $this->makers[] = $maker;
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
    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Picture|null
     */
    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    /**
     * @param Picture|null $picture
     * @return Version
     */
    public function setPicture(?Picture $picture): Version
    {
        $this->picture = $picture;
        return $this;
    }
}
