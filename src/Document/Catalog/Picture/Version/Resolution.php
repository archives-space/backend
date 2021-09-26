<?php

namespace App\Document\Catalog\Picture\Version;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\EmbeddedDocument
 */
class Resolution
{
    /**
     * @var integer
     * @Odm\Field(type="int")
     */
    private int $width;

    /**
     * @var integer
     * @Odm\Field(type="int")
     */
    private int $height;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\Choice({"original", "sm", "md"})
     */
    private string $slug;

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Resolution
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Resolution
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Resolution
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }
}
