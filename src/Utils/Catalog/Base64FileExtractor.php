<?php

namespace App\Utils\Catalog;

class Base64FileExtractor
{
    /**
     * @param string $base64Content
     * @return mixed|string
     */
    public function extractBase64String(string $base64Content)
    {

        $data = explode( ';base64,', $base64Content);
        return $data[1];

    }
}