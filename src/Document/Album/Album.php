<?php

namespace App\Document\Album;

use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

class Album
{
    /**
     * @var string
     * @Odm\Id
     */
    private $id;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $name;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $description;

    /**
     * @var Album|null
     * @ReferenceMany(targetDocument=Album::class, mappedBy="childrens")
     */
    private $parent;

    /**
     * @var Album[]
     * @ReferenceMany(targetDocument=Album::class, inversedBy="parent")
     */
    private $childrens = [];

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

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime("NOW"));
    }

    /**
     * @return string
     */
    public function getId(): string
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
     * @return Album
     */
    public function setName(?string $name): Album
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
     * @return Album
     */
    public function setDescription(?string $description): Album
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Album|null
     */
    public function getParent(): ?Album
    {
        return $this->parent;
    }

    /**
     * @param Album|null $parent
     * @return Album
     */
    public function setParent(?Album $parent): Album
    {
        $parent->addChildren($this);
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Album
     */
    public function getChildrens(): ?Album
    {
        return $this->childrens;
    }

    /**
     * @param Album $children
     * @return Album
     */
    public function addChildren(Album $children): Album
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
     * @return Album
     */
    public function setCreatedAt(\DateTime $createdAt): Album
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
     * @return Album
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Album
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}