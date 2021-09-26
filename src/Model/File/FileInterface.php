<?php

namespace App\Model\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileInterface
{
    /**
     * @param string $name
     * @return FileBase
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getMimeType(): string;

    /**
     * @param string $mimeType
     * @return FileBase
     */
    public function setMimeType(string $mimeType): self;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * @param string $hash
     * @return FileBase
     */
    public function setHash(string $hash): self;

    /**
     * @param string $originalFileName
     * @return FileBase
     */
    public function setOriginalFileName(string $originalFileName): self;

    /**
     * @return string
     */
    public function getOriginalFileName(): string;

    /**
     * @param int $size
     * @return FileBase
     */
    public function setSize(int $size): self;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * @return UploadedFile
     */
    public function getUploadedFile(): UploadedFile;

    /**
     * @param UploadedFile $uploadedFile
     * @return FileBase
     */
    public function setUploadedFile(UploadedFile $uploadedFile): self;
}