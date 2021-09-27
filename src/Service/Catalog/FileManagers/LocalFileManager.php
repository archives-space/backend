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
        $uploadedFile->move($this->getUploadRootDir(), $this->file->getPath());

        if ($this->file->getTemp()) {
            @unlink($this->getUploadRootDir() . '/' . $this->file->getTemp());
            $this->file->setTemp(null);
        }
        $this->file->setTemp(null);
        return true;
    }

    /**
     * @param Picture $picture
     * @return mixed
     */
    public function remove(Picture $picture): bool
    {
        $this->file = $picture->getFile();
        $filePath   = $this->getUploadRootDir() . '/' . $this->file->getTemp();

        if (is_file($filePath)) {
            @unlink($this->file);
        }
        return true;
    }

    protected function getUploadRootDir()
    {
        return $this->kernel->getProjectDir() . '/public/uploads' . $this->file->getUploadDir();
    }
}