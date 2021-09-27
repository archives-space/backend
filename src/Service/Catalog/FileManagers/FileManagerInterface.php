<?php

namespace App\Service\Catalog\FileManagers;

use App\Document\Catalog\Picture;

interface FileManagerInterface
{
    public function upload(Picture $picture): bool;

    public function remove(Picture $picture): bool;
}