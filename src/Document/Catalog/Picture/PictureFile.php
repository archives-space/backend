<?php

namespace App\Document\Catalog\Picture;

use App\Model\File\FileBase;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Odm\EmbeddedDocument
 */
class PictureFile extends FileBase
{
    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private string $name;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private string $mimeType;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private string $originalFileName;

    /**
     * @var integer
     * @Odm\Field(type="int")
     */
    private int $size;

    /**
     * A sha256 hash
     * @var string
     * @Odm\Field(type="string")
     */
    private string $hash;

    /**
     * @var UploadedFile
     */
    private UploadedFile $uploadedFile;

    /**
     * @param string $name
     * @return PictureFile
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return PictureFile
     */
    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return PictureFile
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param string $originalFileName
     * @return PictureFile
     */
    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    /**
     * @param int $size
     * @return PictureFile
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
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
     * @return UploadedFile
     */
    public function getUploadedFile(): UploadedFile
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return PictureFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile): self
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }
}