<?php

namespace App\Model\File;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\EmbeddedDocument
 */
abstract class FileBase implements FileInterface
{
    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $path;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $mimeType;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $originalFileName;

    /**
     * @var integer
     * @Odm\Field(type="int")
     */
    private $size;

    /**
     * A sha256 hash
     * @var string
     * @Odm\Field(type="string")
     */
    private $hash;

    /**
     * @var UploadedFile
     */
    private $uploadedFile;

    /**
     * @var string
     */
    private $temp;

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path): self
    {
        $this->path = $path;
        return $this;
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
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param string $originalFileName
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
     * @return File
     */
    public function getUploadedFile(): File
    {
        return $this->uploadedFile;
    }

    /**
     * @param File $uploadedFile
     */
    public function setUploadedFile(File $uploadedFile): self
    {
        $this->uploadedFile = $uploadedFile;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTemp(): ?string
    {
        return $this->temp;
    }

    /**
     * @param string|null $temp
     * @return FileBase
     */
    public function setTemp(?string $temp): FileBase
    {
        $this->temp = $temp;
        return $this;
    }
}