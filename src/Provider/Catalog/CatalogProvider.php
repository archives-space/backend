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
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CatalogProvider extends BaseProvider
{
    /**
     * @var CatalogRepository
     */
    private CatalogRepository $catalogRepository;

    /**
     * @var CatalogTransformer
     */
    private CatalogTransformer $catalogTransformer;

    public function __construct(
        RequestStack $requestStack,
        CatalogRepository $catalogRepository,
        CatalogTransformer $catalogTransformer
    )
    {
        parent::__construct($requestStack);
        $this->catalogRepository  = $catalogRepository;
        $this->catalogTransformer = $catalogTransformer;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws ExceptionInterface
     */
    public function findById(string $id): ApiResponse
    {
        if ($id === 'root') {
            $catalog = $this->catalogRepository->getRootCatalog();
        } else if (!$catalog = $this->catalogRepository->getCatalogById($id)) {
            $this->apiResponse->addError(Errors::CATALOG_NOT_FOUND);
            return $this->apiResponse;
        }

        return $this->apiResponse
            ->setData($this->catalogTransformer->toArray($catalog))
            ->setNbTotalData(1)
            ;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException|ExceptionInterface
     */
    public function findAll(): ApiResponse
    {
        $data     = $this->catalogRepository->getAllCatalogsPaginate($this->nbPerPage, $this->page);
        $catalogs = array_map(
            fn(Catalog $picture) => $this->catalogTransformer->toArray($picture, false),
            $data[BaseProvider::RESULT]->toArray()
        );
        $this->apiResponse
            ->setData($catalogs)
            ->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT])
        ;
        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws ExceptionInterface
     */
    public function getRoot(): ApiResponse
    {
        $catalog = $this->catalogRepository->getRootCatalog();

        return $this->apiResponse
            ->setData($this->catalogTransformer->toArray($catalog))
            ->setNbTotalData(1)
            ;
    }
}