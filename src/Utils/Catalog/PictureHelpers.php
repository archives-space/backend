<?php

namespace App\Utils\Catalog;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureHelpers
{
    /**
     * @var Base64FileExtractor
     */
    private $base64FileExtractor;

    /**
     * PictureHelpers constructor.
     * @param Base64FileExtractor $base64FileExtractor
     */
    public function __construct(
        Base64FileExtractor $base64FileExtractor
    )
    {
        $this->base64FileExtractor = $base64FileExtractor;
    }

    /**
     * @param string $base64
     * @param string $originalFilename
     * @return UploadedBase64File
     */
    public function base64toImage(string $base64, string $originalFilename)
    {
        $base64Image = $this->base64FileExtractor->extractBase64String($base64);
        return new UploadedBase64File($base64Image, $originalFilename);
    }

    /**
     * @param UploadedFile|UploadedBase64File $file
     * @return string
     */
    public static function getHash($file)
    {
        return hash('sha256', sprintf('%s-%s', $file->getClientOriginalName(), $file->getSize()));
    }
}