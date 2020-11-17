<?php

namespace App\Provider\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\Catalog\CatalogRepository;
use App\Utils\Catalog\CatalogArrayGenerator;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\MongoDBException;

class CatalogProvider
{
    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogArrayGenerator
     */
    private $catalogArrayGenerator;

    /**
     * CatalogProvider constructor.
     * @param CatalogRepository     $catalogRepository
     * @param CatalogArrayGenerator $catalogArrayGenerator
     */
    public function __construct(
        CatalogRepository $catalogRepository,
        CatalogArrayGenerator $catalogArrayGenerator
    )
    {
        $this->catalogRepository     = $catalogRepository;
        $this->catalogArrayGenerator = $catalogArrayGenerator;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function getCatalogById(string $id)
    {
        if (!$picture = $this->catalogRepository->getCatalogById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_CATALOG));
        }

        return (new ApiResponse($this->catalogArrayGenerator->toArray($picture)));
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function getCatalogs()
    {
        $pictures = array_map(function (Catalog $picture) {
            return $this->catalogArrayGenerator->toArray($picture);
        }, $this->catalogRepository->getAllCatalogs()->toArray());

        return (new ApiResponse($pictures));
    }
}