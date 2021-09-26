<?php

namespace App\Document\Catalog\Picture;

use App\Document\File;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @EmbeddedDocument
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
     * @var File
     * @EmbedOne(targetDocument=File::class)
     */
    private File $file;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\Choice({"original", "sm", "md"})
     */
    private string $slug;

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     * @return Resolution
     */
    public function setFile(File $file): self
    {
        $this->file = $file;
        return $this;
    }

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
