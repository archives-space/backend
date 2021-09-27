<?php

namespace App\EventListener\Document\Catalog;

use App\Document\Catalog\Picture;

class PictureUpdate
{
    public function __construct()
    {
    }

    public function postPersist(Picture $picture, LifecycleEventArgs $event): void
    {
        dump($picture);
        dump($event);
        die;

    }
}