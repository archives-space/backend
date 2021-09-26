<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\ObjectChange;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait ObjectChangeTrait
{
    /**
     * @Odm\EmbedMany(targetDocument=ObjectChange::class)
     */
    private $objectChanges;

    /**
     * @return PersistentCollection
     */
    public function getObjectChanges(): PersistentCollection
    {
        return $this->objectChanges;
    }

    /**
     * @param ObjectChange[] $objectChanges
     * @return Version
     */
    public function setObjectChanges(array $objectChanges): Version
    {
        $this->objectChanges = $objectChanges;
        return $this;
    }

    /**
     * @param ObjectChange $objectChange
     * @return Version
     */
    public function addObjectChange(ObjectChange $objectChange): Version
    {
        $this->objectChanges[] = $objectChange;
        return $this;
    }
}