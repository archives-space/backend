<?php

namespace App\Service\Catalog\FileManagers;

use App\Document\Catalog\Picture;

interface FileManagerInterface
{
    public function upload(Picture $picture): bool;

    public function getWebPath(Picture $picture): string;

    public function remove(Picture $picture): bool;
}