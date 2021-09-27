<?php

namespace App\Document\Catalog\Picture\Place;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\EmbeddedDocument
 */
class Position
{
    /**
     * @var float
     * @Odm\Field(type="float")
     */
    private $lat;

    /**
     * @var float
     * @Odm\Field(type="float")
     */
    private $lng;

    /**
     * @param float $lat
     * @param float $lng
     */
    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * @return float
     */
    public function getLat(): float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     * @return self
     */
    public function setLat(float $lat): self
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return float
     */
    public function getLng(): float
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     * @return self
     */
    public function setLng(float $lng): self
    {
        $this->lng = $lng;
        return $this;
    }
}