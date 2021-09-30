<?php

namespace App\Utils\Catalog;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedBase64File extends UploadedFile
{
    /**
     * UploadedBase64File constructor.
     * @param string      $base64String
     * @param string      $originalName
     * @param string|null $mimeType
     * @param int|null    $error
     * @param bool        $test
     */
    public function __construct(string $base64String, string $originalName, string $mimeType = null, int $error = null, bool $test = true)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $data     = base64_decode($base64String);
        file_put_contents($filePath, $data);

        parent::__construct($filePath, $originalName, $mimeType, $error, $test);
    }
}