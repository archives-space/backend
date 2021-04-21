<?php

namespace App\Document\Catalog;

use App\Repository\Catalog\PictureRepository;
use App\Utils\StringManipulation;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Index;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Odm\Document(repositoryClass=PictureRepository::class)
 * @Odm\HasLifecycleCallbacks()
 * @Index(keys={"position"="2d"})
 */
class Picture
{
    const UPLOAD_DIR = '/picture';

    /**
     * @Odm\Id(strategy="INCREMENT")
     */
    private $id;

# métas entré par l'user

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $name;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     */
    private $description;

    /**
     * @var string|null
     * @Odm\Field(type="string")
     * @Assert\NotNull
     */
    private $source;

    /**
     * @var bool
     * @Odm\Field(type="bool")
     */
    private $edited;

    /**
     * @Assert\NotNull
     */
    private $file;

    /**
     * @var string
     * @Odm\Field(type="string")
     * @Assert\NotNull
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
     * @var Exif|null
     * @EmbedOne(targetDocument=Exif::class)
     */
    private $exif;

    /**
     * @var Resolution
     * @Odm\EmbedMany(targetDocument=Resolution::class)
     */
    private $resolutions;

    /**
     * @var Position|null
     * @EmbedOne(targetDocument=Position::class)
     */
    private $position;

    /**
     * @var Position|null
     * @EmbedOne(targetDocument=License::class)
     * @Assert\Valid
     */
    private $license;

    /**
     * @var Catalog|null
     * @ReferenceOne(targetDocument=Catalog::class)
     */
    private $catalog;

    /**
     * @var Place|null
     * @ReferenceOne(targetDocument=Place::class)
     */
    private $place;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime("NOW"));
        $this->setEdited(false);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return StringManipulation::slugify($this->getName());
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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Picture
     */
    public function setDescription(?string $description): Picture
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     * @return Picture
     */
    public function setSource(?string $source): Picture
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
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     * @return Picture
     */
    public function setFile($file)
    {
        $this->file = $file;
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
     * @return Exif|null
     */
    public function getExif(): ?Exif
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
     * @return Position|null
     */
    public function getPosition(): ?Position
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

    /**
     * @return License|null
     */
    public function getLicense(): ?License
    {
        return $this->license;
    }

    /**
     * @param License|null $license
     * @return Picture
     */
    public function setLicense(?License $license): Picture
    {
        $this->license = $license;
        return $this;
    }

    /**
     * @Odm\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime('NOW'));
    }

    /**
     * @return Catalog|null
     */
    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    /**
     * @param Catalog|null $catalog
     * @return Picture
     */
    public function setCatalog(?Catalog $catalog): Picture
    {
        $this->catalog = $catalog;
        return $this;
    }

    /**
     * @return Place|null
     */
    public function getPlace(): ?Place
    {
        return $this->place;
    }

    /**
     * @param Place|null $place
     * @return Picture
     */
    public function setPlace(?Place $place): Picture
    {
        $this->place = $place;
        return $this;
    }
}