<?php

namespace App\Service\Catalog\FileManagers;

use App\Document\Catalog\Picture;
use App\Model\File\FileInterface;
use Aws\S3\S3Client;

class S3FileManager implements FileManagerInterface
{
    /**
     * @var FileInterface
     */
    private FileInterface $file;

    /**
     * @var S3Client
     */
    private S3Client $s3Client;

    private function initS3Client()
    {
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $_ENV['S3_REGION'],
            'endpoint'    => $_ENV['S3_ENDPOINT'],
            'credentials' => [
                'key'    => $_ENV['S3_ACCESS_KEY'],
                'secret' => $_ENV['S3_SECRET_KEY'],
            ],
        ]);
        $this->bucket   = $_ENV['S3_BUCKET'];
        $this->baseUrl  = $_ENV['S3_ENDPOINT'];

        $url           = parse_url($_ENV['S3_ENDPOINT']);
        $this->baseUrl = $url['scheme'] . '://' . $_ENV['S3_BUCKET'] . '.' . $url['host'];
    }

    /**
     * @param Picture $picture
     * @return mixed
     */
    public function upload(Picture $picture): bool
    {
        try {
            $this->file = $picture->getFile();
            $this->initS3Client();
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->file->getPath(),
                'Body'   => fopen($this->file->getUploadedFile()->getRealPath(), 'r'),
                'ACL'    => 'public-read',
            ]);
            return true;
        } catch (Aws\S3\Exception\S3Exception $e) {
            return false;
        }
    }

    /**
     * @param Picture $picture
     * @return mixed
     */
    public function remove(Picture $picture): bool
    {
        try {
            $this->file = $picture->getFile();
            $this->initS3Client();
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $this->file->getPath(),
            ]);
            return true;
        } catch (Aws\S3\Exception\S3Exception $e) {
            return false;
        }
    }
}