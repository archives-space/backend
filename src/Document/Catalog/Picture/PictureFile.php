<?php

namespace App\Document\Catalog\Picture;

use App\Model\File\FileBaseTrait;
use App\Model\File\FileInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\EmbeddedDocument
 */
class PictureFile implements FileInterface
{
    use FileBaseTrait;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private string $typeUpload;

    /**
     * @return string
     */
    public function getTypeUpload(): string
    {
        return $this->typeUpload;
    }

    /**
     * @param string $typeUpload
     * @return PictureFile
     */
    public function setTypeUpload(string $typeUpload): PictureFile
    {
        $this->typeUpload = $typeUpload;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return '/catalog/picture';
    }
}