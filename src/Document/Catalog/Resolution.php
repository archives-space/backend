<?php

namespace App\Document\Catalog;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;

/**
 * @Odm\Document(repositoryClass=ResolutionRepository::class)
 */
class Resolution
{
    /**
     * @Odm\Id
     */
    private $id;

//    /**
//     * @Odm\Field(type="file")
//     */
//    private $file;

    /**
     * @var integer|null
     * @Odm\Field(type="int")
     */
    private $width;

    /**
     * @var integer|null
     * @Odm\Field(type="int")
     */
    private $height;

    /**
     * @var integer
     * @Odm\Field(type="int")
     */
    private $size;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $sizeLabel;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $key;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     * @return Resolution
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     * @return Resolution
     */
    public function setWidth(?int $width): Resolution
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     * @return Resolution
     */
    public function setHeight(?int $height): Resolution
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return Resolution
     */
    public function setSize(int $size): Resolution
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return string
     */
    public function getSizeLabel(): string
    {
        return $this->sizeLabel;
    }

    /**
     * @param string $sizeLabel
     * @return Resolution
     */
    public function setSizeLabel(string $sizeLabel): Resolution
    {
        $this->sizeLabel = $sizeLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Resolution
     */
    public function setKey(string $key): Resolution
    {
        $this->key = $key;
        return $this;
    }
}