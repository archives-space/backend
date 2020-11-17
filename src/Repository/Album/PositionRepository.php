<?php

namespace App\Repository\Album;

use App\Document\Album\Position;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class PositionRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }
}