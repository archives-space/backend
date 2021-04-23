<?php

namespace App\Document;

use App\Utils\FileManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @EmbeddedDocument
 */
class File {
    /**
     * Name of the file Key used by the filesystem to identify the file
     * example: picture.png
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
     * @param string $name
     * @return File
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
     * @return File
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
     * @return File
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @param string $originalFileName
     * @return File
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
     * @return File
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

    public function getUrl(FileManager $fileManager): string
    {
        return $fileManager->getBaseUrl() . '/' . $this->name;
    }

    public function isImage(): string
    {
        return in_array($this->mimeType, ['image/png', 'image/jpeg']);
    }
}
