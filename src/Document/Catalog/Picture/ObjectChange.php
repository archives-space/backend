<?php

namespace App\Document\Catalog\Picture;

use App\Document\User\User;
use App\Utils\Catalog\ObjectChangeHelper;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;

/**
 * @EmbeddedDocument
 */
class ObjectChange
{
    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $status;

    /**
     * @var User|null
     * @Odm\ReferenceMany(targetDocument=User::class)
     */
    private $createdBy;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $field;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $value;

    public function __construct()
    {
        $this->setStatus(ObjectChangeHelper::STATUS_PENDING);
        $this->setCreatedAt(new \DateTime('NOW'));
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ObjectChange
     */
    public function setStatus(string $status): ObjectChange
    {
        $this->status = $status;
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
     * @return ObjectChange
     */
    public function setCreatedBy(?User $createdBy): ObjectChange
    {
        $this->createdBy = $createdBy;
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
     * @return ObjectChange
     */
    public function setCreatedAt(?\DateTime $createdAt): ObjectChange
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return ObjectChange
     */
    public function setField(string $field): ObjectChange
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ObjectChange
     */
    public function setValue(string $value): ObjectChange
    {
        $this->value = $value;
        return $this;
    }
}