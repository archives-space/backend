<?php

namespace App\Document\Album;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Index;
use Doctrine\ODM\MongoDB\PersistentCollection;

/**
 * @Odm\Document(repositoryClass=PictureRepository::class)
 * @Index(keys={"position"="2d"})
 */
class Picture
{
    const UPLOAD_DIR = '/picture';

    /**
     * @Odm\Id
     */
    private $id;

//private $albumId

//private $placeId;

# métas entré par l'user

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $name;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $description;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $source;

    /**
     * @var bool
     * @Odm\Field(type="boolean")
     */
    private $edited;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $originalFileName;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $typeMime;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $checksum;

    /**
     * @var string
     * @Odm\Field(type="string")
     */
    private $hash; // sha256

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $takenAt;

    /**
     * @var \DateTime
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @Odm\Field(type="date")
     */
    private $updatedAt;

    /**
     * @var Exif
     * @EmbedOne(targetDocument=Exif::class)
     */
    private $exif;

    /**
     * @var Resolution
     * @Odm\EmbedMany(targetDocument=Resolution::class)
     */
    private $resolutions;

    /**
     * @var Position
     * @EmbedOne(targetDocument=Position::class)
     */
    private $position;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime("NOW"));
        $this->setEdited(false);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Picture
     */
    public function setName(string $name): Picture
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Picture
     */
    public function setDescription(string $description): Picture
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Picture
     */
    public function setSource(string $source): Picture
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->edited;
    }

    /**
     * @param bool $edited
     * @return Picture
     */
    public function setEdited(bool $edited): Picture
    {
        $this->edited = $edited;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    /**
     * @param string $originalFileName
     * @return Picture
     */
    public function setOriginalFileName(string $originalFileName): Picture
    {
        $this->originalFileName = $originalFileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeMime(): string
    {
        return $this->typeMime;
    }

    /**
     * @param string $typeMime
     * @return Picture
     */
    public function setTypeMime(string $typeMime): Picture
    {
        $this->typeMime = $typeMime;
        return $this;
    }


    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @param string $checksum
     * @return Picture
     */
    public function setChecksum(string $checksum): Picture
    {
        $this->checksum = $checksum;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return Picture
     */
    public function setHash(string $hash): Picture
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTakenAt(): ?\DateTime
    {
        return $this->takenAt;
    }

    /**
     * @param \DateTime|null $takenAt
     * @return Picture
     */
    public function setTakenAt(?\DateTime $takenAt): Picture
    {
        $this->takenAt = $takenAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Picture
     */
    public function setCreatedAt(\DateTime $createdAt): Picture
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return Picture
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Picture
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    /**
     * @return Exif
     */
    public function getExif(): Exif
    {
        return $this->exif;
    }

    /**
     * @param Exif $exif
     * @return Picture
     */
    public function setExif(Exif $exif): Picture
    {
        $this->exif = $exif;
        return $this;
    }

    /**
     * @return PersistentCollection
     */
    public function getResolutions(): PersistentCollection
    {
        return $this->resolutions;
    }

    /**
     * @param Resolution $resolution
     * @return Picture
     */
    public function addResolution(Resolution $resolution): Picture
    {
        $this->resolutions[] = $resolution;
        return $this;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return Picture
     */
    public function setPosition(Position $position): Picture
    {
        $this->position = $position;
        return $this;
    }
}