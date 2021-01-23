<?php

namespace App\Provider\Catalog;

use App\DataTransformer\Catalog\CatalogTransformer;
use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\CatalogRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogProvider extends BaseProvider
{
    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogTransformer
     */
    private $catalogTransformer;

    public function __construct(
        RequestStack $requestStack,
        CatalogRepository $catalogRepository,
        CatalogTransformer $catalogTransformer
    )
    {
        parent::__construct($requestStack);
        $this->catalogRepository     = $catalogRepository;
        $this->catalogTransformer = $catalogTransformer;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id)
    {
        if (!$picture = $this->catalogRepository->getCatalogById($id)) {
            return (new ApiResponse(null, Errors::CATALOG_NOT_FOUND));
        }

        $this->apiResponse->setData($this->catalogTransformer->toArray($picture))->setNbTotalData(1);
        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function findAll()
    {
        $data      = $this->catalogRepository->getAllCatalogsPaginate($this->nbPerPage, $this->page);

        $catalogs = array_map(function (Catalog $picture) {
            return $this->catalogTransformer->toArray($picture, false);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($catalogs)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}