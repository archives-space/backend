<?php

namespace App\Repository\Catalog;

use App\Document\Catalog\Place;
use App\Provider\BaseProvider;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

class PlaceRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    /**
     * @param string $id
     * @return Place|array|object|null
     */
    public function getPlaceById(string $id)
    {
        return $this->createQueryBuilder('u')
                    ->field('id')->equals($id)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @return Place[]|array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllPlaces()
    {
        return $this->createQueryBuilder('u')
                    ->sort('name', 'ASC')
                    ->getQuery()
                    ->execute()
            ;
    }

    /**
     * @param int|null $nbPerPage
     * @param int|null $page
     * @return Place[]|array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllPlacesPaginate(?int $nbPerPage = 10, ?int $page = 1)
    {
        $qb = $this->createQueryBuilder('u')
                   ->sort('name', 'ASC')
        ;

        $nbTotalResult = count($qb->getQuery()->execute()->toArray());

        if ($nbPerPage) {
            $qb->limit($nbPerPage)
               ->skip($page ?: 1)
            ;
        }

        return [
            BaseProvider::NB_TOTAL_RESULT => $nbTotalResult,
            BaseProvider::RESULT          => $qb->getQuery()->execute(),
        ];
    }
}