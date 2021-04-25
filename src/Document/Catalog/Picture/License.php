<?php

namespace App\Document\Catalog\Picture;

use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @EmbeddedDocument
 */
class License
{
    /**
     * @var string|null
     * @Field(type="string")
     * @Assert\NotNull
     * @App\Validator\Catalog\PictureLicense
     */
    private $name;

    /**
     * @var boolean|null
     * @Field(type="bool")
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