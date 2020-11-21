<?php

namespace App\Provider\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\CatalogRepository;
use App\Utils\Catalog\CatalogArrayGenerator;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogProvider extends BaseProvider
{
    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogArrayGenerator
     */
    private $catalogArrayGenerator;

    public function __construct(
        RequestStack $requestStack,
        CatalogRepository $catalogRepository,
        CatalogArrayGenerator $catalogArrayGenerator
    )
    {
        parent::__construct($requestStack);
        $this->catalogRepository     = $catalogRepository;
        $this->catalogArrayGenerator = $catalogArrayGenerator;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id)
    {
        if (!$picture = $this->catalogRepository->getCatalogById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_CATALOG));
        }

        $this->apiResponse->setData($this->catalogArrayGenerator->toArray($picture))->setNbTotalData(1);
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
            return $this->catalogArrayGenerator->toArray($picture, false);
        }, $data[BaseProvider::RESULT]->toArray());
        $this->apiResponse->setData($catalogs)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}