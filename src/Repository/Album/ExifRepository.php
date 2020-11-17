<?php

namespace App\Repository\Album;

use App\Document\Album\Exif;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class ExifRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exif::class);
    }
}