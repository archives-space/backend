<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Version\ObjectChange;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait ObjectChangeTrait
{
    /**
     * @Odm\ReferenceMany(targetDocument=ObjectChange::class)
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
     */
    public function setObjectChanges(array $objectChanges): self
    {
        $this->objectChanges = $objectChanges;
        return $this;
    }

    /**
     * @param ObjectChange $objectChange
     */
    public function addObjectChange(ObjectChange $objectChange): self
    {
        $this->objectChanges[] = $objectChange;
        return $this;
    }
}