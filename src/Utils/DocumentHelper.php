<?php

namespace App\Utils;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Builder;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Helper to get done regular operations on documents with MongoDB ORM
 */
class DocumentHelper
{
    /**
     * Get a Doctrine MongoDB Query Builder
     *
     * @param ContainerInterface $container
     * @param string $documentClass
     * @return Builder
     */
    public static function getQueryBuilder(ContainerInterface $container, string $documentClass): Builder
    {
        $dm = $container->get(DocumentManager::class);
        return $dm->createQueryBuilder($documentClass);
    }

    /**
     * @param ContainerInterface $container
     * @param string $documentClass
     * @return array
     * @throws MongoDBException
     */
    public static function getAll(ContainerInterface $container, string $documentClass): array
    {
        $qb = self::getQueryBuilder($container, $documentClass);
        return $qb->getQuery()->execute()->toArray();
    }

    /**
     * Paginate a Query Builder
     *
     * @param Builder $queryBuilder
     * @param int $nbPerPage
     * @param int $page
     * @return array
     * @throws MongoDBException
     */
    public static function paginate(Builder $queryBuilder, int $nbPerPage, int $page): array
    {
        $nbTotalResult = count($queryBuilder->getQuery()->execute()->toArray());
        $pagesCount = 1;
        if ($nbPerPage != 0) {
            $pagesCount = (int)($nbTotalResult / $nbPerPage + ($nbTotalResult % $nbPerPage == 0 ? 0 : 1));
        }

        if ($nbPerPage != null) {
            $queryBuilder = $queryBuilder
                ->skip($page * $nbPerPage ?? 0)
                ->limit($nbPerPage);
        }

        return [
            'meta' => [
                'totalCount' => $nbTotalResult,
                'pagesCount' => $pagesCount
            ],
            'data' => $queryBuilder->getQuery()->execute()
        ];
    }

    /**
     * Get one item by ID
     *
     * @param ContainerInterface $container
     * @param string $documentClass
     * @param string $id
     * @return array|object|null
     */
    public static function getOne(ContainerInterface $container, string $documentClass, string $id)
    {
        return $container->get(DocumentManager::class)
            ->createQueryBuilder($documentClass)
            ->field('id')->equals($id)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Persist change into DB
     *
     * @param ContainerInterface $container
     * @throws MongoDBException
     */
    public static function flush(ContainerInterface $container): void
    {
        $container->get(DocumentManager::class)->flush();
    }

    /**
     * @param ContainerInterface $container
     * @param object $item
     * @return mixed
     * @throws MongoDBException
     */
    public static function persistAndFlush(ContainerInterface $container, object $item): void
    {
        $dm = $container->get(DocumentManager::class);
        $item->fillTimestamps();
        $dm->persist($item);
        $dm->flush();
    }

    /**
     * Create and persist a item into MongoDB
     *
     * @param ContainerInterface $container
     * @param object $document
     * @return object
     * @throws MongoDBException
     */
    public static function create(ContainerInterface $container, object $document): object
    {
        $dm = $container->get(DocumentManager::class);
        if ((new ReflectionClass($document))->hasProperty('createdAt')) {
            $document->fillTimestamps();
        }
        $dm->persist($document);
        $dm->flush();

        return $document;
    }

    /**
     * Delete a item by IDs
     *
     * @param ContainerInterface $container
     * @param string $documentClass
     * @param array $ids
     * @return array
     * @throws MongoDBException
     */
    public static function delete(ContainerInterface $container, string $documentClass, array $ids): array
    {
        $dm =     $container->get(DocumentManager::class);
        $res = $dm
            ->createQueryBuilder($documentClass)->remove()
            ->field('id')->in($ids)
            ->getQuery()->execute();
        $dm->flush();

        return $res->getDeletedCount();
    }
}