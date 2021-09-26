<?php

namespace App\Repository\Catalog\Picture;

use App\Document\Catalog\Picture\Version\ObjectChange;
use App\Utils\Catalog\ObjectChangeHelper;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

class ObjectChangeRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObjectChange::class);
    }

    /**
     * @param array $ids
     * @return array|\Doctrine\ODM\MongoDB\Iterator\Iterator|int|\MongoDB\DeleteResult|\MongoDB\InsertOneResult|\MongoDB\UpdateResult|object|null
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getByIds(array $ids)
    {
        return $this->createQueryBuilder('u')
                    ->field('id')->in($ids)
                    ->field('status')->equals(ObjectChangeHelper::STATUS_PENDING)
                    ->sort('createdAt', 'ASC')
                    ->getQuery()
                    ->execute()
            ;
    }
}