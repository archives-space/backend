<?php

namespace App\Document\Catalog;

use App\Repository\Catalog\CatalogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\Document(repositoryClass=CatalogRepository::class)
 * @Odm\HasLifecycleCallbacks()
 */
class Catalog
{
    /**
     * @var string|null
     * @Odm\Id
     */
    private $id;

    /**
     * @var string|null
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
     * @var Catalog|null
     * @ReferenceOne(targetDocument=Catalog::class, mappedBy="childrens")
     */
    private $parent;

    /**
     * @var PersistentCollection
     * @ReferenceMany(targetDocument=Catalog::class, inversedBy="parent")
     */
    private $childrens;

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
    private $pictures = [];

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime("NOW"));
        $this->childrens = new ArrayCollection();
    }

    /**
     * @param string|null $id
     * @return Catalog
     */
    public function setId(?string $id): Catalog
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Catalog
     */
    public function setName(?string $name): Catalog
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
     * @return Catalog
     */
    public function setDescription(?string $description): Catalog
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Catalog|null
     * @MaxDepth(1)
     */
    public function getParent(): ?Catalog
    {
        return $this->parent;
    }

    /**
     * @param Catalog|null $parent
     * @return Catalog
     */
    public function setParent(?Catalog $parent): Catalog
    {
        if ($parent) {
            $parent->addChildren($this);
        }
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param Catalog $parent
     * @return Catalog
     */
    public function removeParent(Catalog $parent): Catalog
    {
//        if (!$parent->getChildrens()->contains($this)) {
//            return $this;
//        }
//        $parent->getChildrens()->removeElement($this);
//        // not needed for persistence, just keeping both sides in sync
        $this->setParent(null);
        return $this;
    }

    /**
     * @return PersistentCollection
     * @MaxDepth(1)
     */
    public function getChildrens(): ?PersistentCollection
    {
        return $this->childrens;
    }

    /**
     * @param Catalog $children
     * @return Catalog
     */
    public function addChildren(Catalog $children): Catalog
    {
        $this->childrens[] = $children;
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
     * @return Catalog
     */
    public function setCreatedAt(\DateTime $createdAt): Catalog
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
     * @return Catalog
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Catalog
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
     * @return Catalog
     */
    public function addPicture(Picture $picture): Catalog
    {
        $picture->setCatalog($this);
        $this->pictures[] = $picture;
        return $this;
    }

    /**
     * @param Picture $picture
     * @return Catalog
     */
    public function removePicture(Picture $picture): Catalog
    {
        if (!$this->getPictures()->contains($picture)) {
            return $this;
        }
        // todo à vérifier
        $this->getChildrens()->removeElement($picture);
        // not needed for persistence, just keeping both sides in sync
        $picture->setCatalog(null);
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