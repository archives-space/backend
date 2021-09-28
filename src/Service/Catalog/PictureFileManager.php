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
     * @var Picture
     */
    private Picture $picture;

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
        $this->picture = $picture;

        // si c'est en upload s3
        if (self::FILE_SOURCE_S3 === $this->fileSource) {
            // // et que l'upload s'est bien passé alors rien
            try{
                $this->s3FileManager->upload($picture);
                return;
            }catch(\Exception $e){

            }
        }

        // par default upload local
        $this->localFileManager->upload($picture);
    }

    public function remove(Picture $picture)
    {
        $this->picture = $picture;
        if (
            self::FILE_SOURCE_S3 === $this->fileSource && // si c'est en upload s3
            $this->s3FileManager->upload($picture)) { // et que l'upload s'est bien passé alors rien
            return;
        }

        // par default upload local
        $this->localFileManager->remove($picture);
    }

}