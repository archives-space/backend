<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\Resolution;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait ResolutionTrait
{
    /**
     * @Odm\EmbedMany(targetDocument=Resolution::class)
     */
    private $resolutions;

    /**
     * @return PersistentCollection
     */
    public function getResolutions(): PersistentCollection
    {
        return $this->resolutions;
    }

    /**
     * @param Resolution $resolution
     * @return Version
     */
    public function addResolution(Resolution $resolution): Version
    {
        $this->resolutions->add($resolution);
        return $this;
    }
}