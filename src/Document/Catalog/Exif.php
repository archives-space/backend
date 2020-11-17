<?php

namespace App\Document\Catalog;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\Document(repositoryClass=ExifRepository::class)
 */
class Exif
{
    /**
     * @Odm\Id
     */
    private $id;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $model;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $manufacturer;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $aperture;

    /**
     * @var int|null
     * @Odm\Field(type="int")
     */
    private $iso;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $exposure;

    /**
     * @var float|null
     * @Odm\Field(type="float")
     */
    private $focalLength;

    /**
     * @var bool|null
     * @Odm\Field(type="boolean")
     */
    private $flash;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string|null $model
     * @return Exif
     */
    public function setModel(?string $model): Exif
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    /**
     * @param string|null $manufacturer
     * @return Exif
     */
    public function setManufacturer(?string $manufacturer): Exif
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAperture(): ?string
    {
        return $this->aperture;
    }

    /**
     * @param string|null $aperture
     * @return Exif
     */
    public function setAperture(?string $aperture): Exif
    {
        $this->aperture = $aperture;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getIso(): ?int
    {
        return $this->iso;
    }

    /**
     * @param int|null $iso
     * @return Exif
     */
    public function setIso(?int $iso): Exif
    {
        $this->iso = $iso;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExposure(): ?string
    {
        return $this->exposure;
    }

    /**
     * @param string|null $exposure
     * @return Exif
     */
    public function setExposure(?string $exposure): Exif
    {
        $this->exposure = $exposure;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getFocalLength(): ?float
    {
        return $this->focalLength;
    }

    /**
     * @param float|null $focalLength
     * @return Exif
     */
    public function setFocalLength(?float $focalLength): Exif
    {
        $this->focalLength = $focalLength;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getFlash(): ?bool
    {
        return $this->flash;
    }

    /**
     * @param bool|null $flash
     * @return Exif
     */
    public function setFlash(?bool $flash): Exif
    {
        $this->flash = $flash;
        return $this;
    }
}