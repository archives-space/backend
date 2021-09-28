<?php

namespace App\Document\Catalog\Picture\Version;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\EmbeddedDocument
 */
class License
{
    /**
     * @var string|null
     * @Odm\Field(type="string")
     * @Assert\NotNull
     * @App\Validator\Catalog\PictureLicense
     */
    private $name;

    /**
     * @var boolean|null
     * @Odm\Field(type="bool")
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
     * @param  $isEdited
     * @return License
     */
    public function setIsEdited($isEdited): License
    {
        $this->isEdited = $isEdited;
        return $this;
    }
}