<?php

namespace App\Repository\Catalog;

use App\Document\Catalog\Catalog;
use App\Provider\BaseProvider;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

class CatalogRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    /**
     * @param string $id
     * @return Catalog|array|object|null
     */
    public function getCatalogById(string $id)
    {
        return $this->createQueryBuilder('u')
                    ->field('id')->equals($id)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @return Catalog[]|array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllCatalogs()
    {
        return $this->createQueryBuilder('u')
                    ->sort('name', 'ASC')
                    ->getQuery()
                    ->execute()
            ;
    }

    /**
     * @return Catalog|array|object
     */
    public function getRootCatalog()
    {
        return $this->createQueryBuilder()
            ->field('name')->equals('root')
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param int $nbPerPage
     * @param int $page
     * @return Catalog[]|array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllCatalogsPaginate(?int $nbPerPage = 10, ?int $page = 1)
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