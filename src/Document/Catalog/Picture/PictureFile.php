<?php

namespace App\Document\Catalog\Picture;

use App\Model\File\FileBase;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\EmbeddedDocument
 */
class PictureFile extends FileBase
{
    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return '/catalog/picture';
    }
}