<?php

namespace App\Repository;

use App\Document\Product;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class ProductRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder()
                    ->sort('name', 'ASC')
                    ->getQuery()
                    ->execute()
            ;
    }
}