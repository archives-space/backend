<?php

namespace App\Document\Catalog;

use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;

/**
 * @EmbeddedDocument
 */
class License
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var boolean|null
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