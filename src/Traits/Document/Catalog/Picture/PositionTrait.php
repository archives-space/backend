<?php

namespace App\Traits\Document\Catalog\Picture;

use App\Document\Catalog\Picture\Version;
use App\Document\Catalog\Picture\Place\Position;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;

trait PositionTrait
{
    /**
     * @Odm\EmbedOne(targetDocument=Position::class)
     */
    private $position;

    /**
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return Version
     */
    public function setPosition(Position $position): Version
    {
        $this->position = $position;
        return $this;
    }
}