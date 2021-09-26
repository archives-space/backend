<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\License;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait LicenseTrait
{
    /**
     * @Odm\EmbedOne(targetDocument=License::class)
     * @Assert\Valid
     */
    private $license;

    /**
     * @return License|null
     */
    public function getLicense(): ?License
    {
        return $this->license;
    }

    /**
     * @param License|null $license
     * @return Version
     */
    public function setLicense(?License $license): Version
    {
        $this->license = $license;
        return $this;
    }
}