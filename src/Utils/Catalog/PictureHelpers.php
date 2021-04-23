<?php

namespace App\Utils\Catalog;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureHelpers
{
    /**
     * @var Base64FileExtractor
     */
    private Base64FileExtractor $base64FileExtractor;

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
    public function base64toImage(string $base64, string $originalFilename): UploadedBase64File
    {
        $base64Image = $this->base64FileExtractor->extractBase64String($base64);
        return new UploadedBase64File($base64Image, $originalFilename);
    }

    /**
     * @param UploadedFile|UploadedBase64File $file
     * @return string
     */
    public static function getHash($file): string
    {
        return hash('sha256', sprintf('%s-%s', $file->getClientOriginalName(), $file->getSize()));
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function hashFile(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    /**
     * @return string[]
     */
    public static function getLicenses(): array
    {
        return [
            'CC BY',
            'CC BY-SA',
            'CC BY-ND',
            'CC BY-NC',
            'CC BY-NC-SA',
            'CC BY-NC-ND',
            'CC BY-SA 3.0 IGO',
            'All rights reserved',
            'Public Domain',
        ];
    }
}