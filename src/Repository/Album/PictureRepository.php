<?php

namespace App\Repository\Album;

use App\Document\Album\Picture;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

class PictureRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Picture::class);
    }

    /**
     * @param string $id
     * @return Picture|array|object|null
     */
    public function getPictureById(string $id)
    {
        return $this->createQueryBuilder('u')
                    ->field('id')->equals($id)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @return Picture[]|array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllPictures()
    {
        return $this->createQueryBuilder('u')
                    ->sort('name', 'ASC')
                    ->getQuery()
                    ->execute()
            ;
    }
}