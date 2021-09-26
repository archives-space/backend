<?php

namespace App\Document\Catalog;

use App\Document\Catalog\Picture\License;
use App\Document\Catalog\Picture\ObjectChange;
use App\Document\Catalog\Picture\Position;
use App\Document\Catalog\Picture\Resolution;
use App\Document\Catalog\Picture\Version;
use App\Repository\Catalog\PictureRepository;
use App\Utils\StringManipulation;
use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Index;
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
     * @var string
     * @Odm\Id(strategy="CUSTOM", type="string", options={"class"="App\Utils\IdGenerator"})
     */
    private $id;

# métas entré par l'user

    /**
     * @var bool
     * @Odm\Field(type="bool")
     */
    private $edited;

    /**
     * @var DateTime
     * @Odm\Field(type="date")
     */
    private $createdAt;

    /**
     * @var DateTime|null
     * @Odm\Field(type="date")
     */
    private $updatedAt;

    /**
     * @var Resolution[]
     * @Odm\EmbedMany(targetDocument=Resolution::class)
     */
    private array $resolutions;

    /**
     * @var Catalog|null
     * @Odm\ReferenceOne(targetDocument=Catalog::class)
     */
    private ?Catalog $catalog;

    /**
     * @var Version
     * @Odm\ReferenceOne(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $validatedVersion;

    /**
     * @var Version[]
     * @Odm\ReferenceMany(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $versions;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime("NOW"));
        $this->setEdited(false);
        $this->versions      = [];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return StringManipulation::slugify($this->get());
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Picture
     */
    public function setCreatedAt(DateTime $createdAt): Picture
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return Picture
     */
    public function setUpdatedAt(?DateTime $updatedAt): Picture
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Resolution[]
     */
    public function getResolutions(): array
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
     * @Odm\PreUpdate()
     */
    public function preUpdate()
    {
        $this->setUpdatedAt(new DateTime('NOW'));
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
     * @return Version
     */
    public function getValidatedVersion(): Version
    {
        return $this->validatedVersion;
    }

    /**
     * @param Version $validatedVersion
     * @return Picture
     */
    public function setValidatedVersion(Version $validatedVersion): self
    {
        $this->validatedVersion = $validatedVersion;
        return $this;
    }

    /**
     * @return PersistentCollection|array
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param Version[] $versions
     * @return Picture
     */
    public function setVersions(array $versions): Picture
    {
        $this->versions = $versions;
        return $this;
    }

    /**
     * @param Version $version
     * @return Picture
     */
    public function addVersion(Version $version): Picture
    {
        $this->versions[] = $version;
        $version->setPicture($this);
        return $this;
    }
}
