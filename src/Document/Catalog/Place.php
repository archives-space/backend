<?php

namespace App\Document\Catalog;

use App\Document\DocumentToArray;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\Document(repositoryClass=PlaceRepository::class)
 * @Odm\HasLifecycleCallbacks()
 */
class Place
{
    /**
     * @Odm\Id
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
     */
    private $wikipedia;

    /**
     * @var Position|null
     * @EmbedOne(targetDocument=Position::class)
     */
    private $position;

    /**
     * @var \DateTime
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $updatedAt;

    /**
     * @var PersistentCollection
     * @ReferenceMany(targetDocument=Picture::class)
     */
    private $pictures;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->setCreatedAt(new \DateTime('NOW'));
    }

    /**
     * @param mixed $id
     * @return Place
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
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
     * @return Place
     */
    public function setName(string $name): Place
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
     * @return Place
     */
    public function setDescription(?string $description): Place
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWikipedia(): ?string
    {
        return $this->wikipedia;
    }

    /**
     * @param string|null $wikipedia
     * @return Place
     */
    public function setWikipedia(?string $wikipedia): Place
    {
        $this->wikipedia = $wikipedia;
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
     * @param Position|null $position
     * @return Place
     */
    public function setPosition(?Position $position): Place
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Place
     */
    public function setCreatedAt(\DateTime $createdAt): Place
    {
        $this->createdAt = $createdAt;
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
     * @return Place
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Place
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getPictures(): PersistentCollection
    {
        return $this->pictures;
    }

    /**
     * @param Picture $picture
     * @return Place
     */
    public function addPicture(Picture $picture): Place
    {
        $picture->setPlace($this);
        $this->pictures[] = $picture;
        return $this;
    }

    /**
     * @param Picture $picture
     * @return Place
     */
    public function removePicture(Picture $picture): Place
    {
        if (!$this->getPictures()->contains($picture)) {
            return $this;
        }
        $this->getPictures()->removeElement($picture);
        // not needed for persistence, just keeping both sides in sync
        $picture->setPlace(null);
        return $this;
    }


    /**
     * @Odm\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime('NOW'));
    }
}