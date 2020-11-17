<?php

namespace App\Document\Album;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\Document(repositoryClass=PositionRepository::class)
 */
class Position
{

    /**
     * @Odm\Id
     */
    private $id;

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
     * Position constructor.
     * @param float $lat
     * @param float $lng
     */
    public function __construct(float $lat, float $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
     * @return Position
     */
    public function setLat(float $lat): Position
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
     * @return Position
     */
    public function setLng(float $lng): Position
    {
        $this->lng = $lng;
        return $this;
    }
}