<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\Place;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait PlaceTrait
{
    /**
     * @Odm\EmbedOne(targetDocument=Place::class)
     */
    private $place;

    /**
     * @return Place|null
     */
    public function getPlace(): ?Place
    {
        return $this->place;
    }

    /**
     * @param Place|null $place
     * @return Version
     */
    public function setPlace(?Place $place): Version
    {
        $this->place = $place;
        return $this;
    }
}