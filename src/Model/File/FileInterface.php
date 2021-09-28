<?php

namespace App\Model\File;

use Symfony\Component\HttpFoundation\File\File;

interface FileInterface
{
    /**
     * @return string
     */
    public function getUploadDir(): string;

    /**
     * @return File
     */
    public function getUploadedFile(): File;

    /**
     * @param File $uploadedFile
     */
    public function setUploadedFile(File $uploadedFile): self;

    /**
     * @return string
     */
    public function getTemp(): ?string;

    /**
     * @param string|null $temp
     * @return FileBase
     */
    public function setTemp(?string $temp): FileBase;
}