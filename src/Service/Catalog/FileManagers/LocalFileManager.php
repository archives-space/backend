<?php

namespace App\Service\Catalog\FileManagers;

use App\Document\Catalog\Picture;
use App\Model\File\FileInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class LocalFileManager implements FileManagerInterface
{
    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var FileInterface
     */
    private FileInterface $file;

    public function __construct(
        KernelInterface $kernel
    )
    {
        $this->kernel = $kernel;
    }

    /**
     * @param Picture $picture
     * @return mixed
     */
    public function upload(Picture $picture): bool
    {
        $this->file = $picture->getFile();

        if (!$uploadedFile = $this->file->getUploadedFile()) {
            return true;
        }
        $filename = $this->getFilename();
        $this->file->setPath($filename . '.' . $uploadedFile->guessExtension());

        $uploadedFile->move($this->getUploadRootDir(), $this->file->getPath());

        if ($this->file->getTemp()) {
            @unlink($this->getUploadRootDir() . '/' . $this->file->getTemp());
            $this->file->setTemp(null);
        }
        $this->file->setTemp(null);
        return true;
    }

    public function getWebPath(Picture $picture): string
    {
        $this->file = $picture->getFile();
        return $this->getPublicUploadDir() . '/' . $this->file ->getPath();
    }


    /**
     * @param Picture $picture
     * @return mixed
     */
    public function remove(Picture $picture): bool
    {
        $this->file = $picture->getFile();
        $filePath = $this->getUploadRootDir() . '/' . $this->file->getPath();

        if (is_file($filePath)) {
            @unlink($filePath);
        }
        return true;
    }


    private function getFilename()
    {
        return substr(sha1(uniqid(mt_rand(), true)), 0, 8);
    }

    private function getUploadRootDir()
    {
        return $this->kernel->getProjectDir() . '/public' . $this->getPublicUploadDir();
    }

    private function getPublicUploadDir()
    {
        return '/uploads' . $this->file->getUploadDir();
    }
}