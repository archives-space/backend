<?php

namespace App\Document\Catalog;

use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;

/**
 * @EmbeddedDocument
 */
class License
{
    /**
     * @var string|null
     * @Field(type="string")
     */
    private $name;

    /**
     * @var boolean|null
     * @Field(type="boolean")
     */
    private $isEdited = false;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return License
     */
    public function setName(?string $name): License
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isEdited(): ?bool
    {
        return $this->isEdited;
    }

    /**
     * @param bool|null $isEdited
     * @return License
     */
    public function setIsEdited(?bool $isEdited): License
    {
        $this->isEdited = $isEdited;
        return $this;
    }
}