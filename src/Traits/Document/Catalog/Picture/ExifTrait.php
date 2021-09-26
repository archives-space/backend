<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\Exif;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait ExifTrait
{
    /**
     * @Odm\EmbedOne(targetDocument=Exif::class)
     */
    private $exif;

    /**
     * @return Exif|null
     */
    public function getExif(): ?Exif
    {
        return $this->exif;
    }

    /**
     * @param Exif $exif
     * @return Version
     */
    public function setExif(Exif $exif): Version
    {
        $this->exif = $exif;
        return $this;
    }
}