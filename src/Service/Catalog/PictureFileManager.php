<?php

namespace App\Service\Catalog;

use App\Document\Catalog\Picture;
use App\Service\Catalog\FileManagers\LocalFileManager;
use App\Service\Catalog\FileManagers\S3FileManager;

class PictureFileManager
{
    const FILE_SOURCE_S3    = 's3';
    const FILE_SOURCE_LOCAL = 'local';

    /**
     * @var string
     */
    private $fileSource;

    /**
     * @var S3FileManager
     */
    private S3FileManager $s3FileManager;

    /**
     * @var LocalFileManager
     */
    private LocalFileManager $localFileManager;

    public function __construct(
        S3FileManager $s3FileManager,
        LocalFileManager $localFileManager
    )
    {

        $this->s3FileManager    = $s3FileManager;
        $this->localFileManager = $localFileManager;
        $this->init();
    }

    private function init()
    {
        if (!$this->fileSource = $_ENV['FILE_SOURCE']) {
            throw new \Exception('Type de file source non spécifié');
        }
        if (!in_array($this->fileSource, [self::FILE_SOURCE_S3, self::FILE_SOURCE_LOCAL])) {
            throw new \Exception(sprintf('Type de file source non reconnus : "%s"', $this->fileSource));
        }
    }

    public function upload(Picture $picture)
    {
        $this->fillPictureFileInfo($picture);


        if (self::FILE_SOURCE_S3 === $this->fileSource) {
            if ($this->s3FileManager->upload($picture)) {
                $picture->getFile()->setTypeUpload(self::FILE_SOURCE_S3);
                return;
            }
        }

        $this->localFileManager->upload($picture);
        $picture->getFile()->setTypeUpload(self::FILE_SOURCE_LOCAL);
    }

    public function getWebPath(Picture $picture)
    {
        if (self::FILE_SOURCE_S3 === $picture->getFile()->getTypeUpload()) {
            if ($webPath = $this->s3FileManager->getWebPath($picture)) {
                return $webPath;
            }
        }

        return $this->localFileManager->getWebPath($picture);
    }

    public function remove(Picture $picture)
    {
        if (self::FILE_SOURCE_S3 === $this->fileSource) {
            if ($this->s3FileManager->remove($picture)) {
                return;
            }
        }

        $this->localFileManager->remove($picture);
    }

    private function fillPictureFileInfo(Picture $picture)
    {
        $picture->getFile()->setSize($picture->getFile()->getUploadedFile()->getSize())
             ->setMimeType($picture->getFile()->getUploadedFile()->getMimeType())
        ;
    }

}