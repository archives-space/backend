<?php

namespace App\Document\Catalog;

use App\Document\Catalog\Picture\PictureFile;
use App\Document\Catalog\Picture\Version;
use App\Repository\Catalog\PictureRepository;
use App\Traits\Document\Catalog\Picture\ObjectChangeTrait;
use App\Utils\StringManipulation;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

/**
 * @Odm\Document(repositoryClass=PictureRepository::class)
 * @Odm\Index(keys={"position"="2d"})
 * @Odm\HasLifecycleCallbacks()
 */
class Picture
{
    const UPLOAD_DIR = '/picture';

    use ObjectChangeTrait;

    /**
     * @Odm\Id
     */
    private $id;

# métas entré par l'user

    /**
     * @Odm\Field(type="string")
     */
    private $originalFilename;

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
     * @var Catalog|null
     * @Odm\ReferenceOne(targetDocument=Catalog::class)
     */
    private $catalog;

    /**
     * @var Version
     * @Odm\ReferenceOne(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $validatedVersion;

    /**
     * @Odm\ReferenceMany(targetDocument=Version::class, cascade={"persist", "remove"})
     */
    private $versions;

    /**
     * @var PictureFile|null
     * @Odm\EmbedOne(targetDocument=PictureFile::class)
     */
    private $file;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime("NOW"));
//        $this->setEdited(false);
        $this->versions = new ArrayCollection();
        $this->objectChanges = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * @param mixed $originalFilename
     * @return Picture
     */
    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
        return $this;
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
     * @return Version|null
     */
    public function getValidatedVersion(): ?Version
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
     * @return ArrayCollection
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
        $this->versions->add($version);
        return $this;
    }

    /**
     * @return PictureFile|null
     */
    public function getFile(): ?PictureFile
    {
        return $this->file;
    }

    /**
     * @param PictureFile|null $pictureFile
     * @return Picture
     */
    public function setFile(?PictureFile $pictureFile): self
    {
        $this->file = $pictureFile;
        return $this;
    }
}
