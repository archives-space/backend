<?php

namespace App\Document\Catalog;

use App\Document\Catalog\Picture\License;
use App\Document\Catalog\Picture\Position;
use App\Document\Catalog\Picture\Resolution;
use App\Document\Catalog\Picture\Version;
use App\Repository\Catalog\PictureRepository;
use App\Utils\StringManipulation;
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
     * @Odm\Id(strategy="INCREMENT")
     */
    private $id;

# métas entré par l'user

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
     * @var Resolution[]
     * @Odm\EmbedMany(targetDocument=Resolution::class)
     */
    private $resolutions;

    /**
     * @var Position|null
     * @EmbedOne(targetDocument=License::class)
     * @Assert\Valid
     */
    private $license;

    /**
     * @var Catalog|null
     * @Odm\ReferenceOne(targetDocument=Catalog::class)
     */
    private $catalog;

    /**
     * @var Version
     * @Odm\ReferenceOne(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $validateVersion;

    /**
     * @var Version[]
     * @Odm\ReferenceMany(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $versions;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime("NOW"));
        $this->setEdited(false);
        $this->versions = [];
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
        return 'toto';
        return StringManipulation::slugify($this->getName());
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
     * @return Version
     */
    public function getValidateVersion(): Version
    {
        return $this->validateVersion;
    }

    /**
     * @param Version $validateVersion
     * @return Picture
     */
    public function setValidateVersion(Version $validateVersion): Picture
    {
        $this->validateVersion = $validateVersion;
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
        return $this;
    }


}