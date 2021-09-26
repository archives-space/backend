<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Picture;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class PictureFileManager
{
    /**
     * @var string
     */
    private $uploadDir;

    public function __construct(
        KernelInterface $kernel
    )
    {
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads';
    }

    /**
     * @param UploadedFile|UploadedBase64File $file
     * @param Picture                         $picture
     */
    public function upload($file, Picture $picture)
    {
//        $file->move($this->uploadDir . Picture::UPLOAD_DIR, $picture->getOriginalFileName());

        // todo pas bon ça : $file->getClientOriginalName()
        $file->move($this->uploadDir . Picture::UPLOAD_DIR, $file->getClientOriginalName());
    }

    /**
     * @param Picture $picture
     */
    public function remove(Picture $picture)
    {
        $path = $this->uploadDir . Picture::UPLOAD_DIR . DIRECTORY_SEPARATOR . $picture->getOriginalFileName();
        if (is_file($path)) {
            unlink($path);
        }
    }
}