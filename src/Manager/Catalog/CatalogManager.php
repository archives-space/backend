<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\CatalogTransformer;
use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\CatalogRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CatalogManager extends BaseManager
{
    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogTransformer
     */
    private $catalogTransformer;

    /**
     * @var Catalog
     */
    private $postedCatalog;

    /**
     * PictureManager constructor.
     * @param DocumentManager    $dm
     * @param RequestStack       $requestStack
     * @param CatalogRepository  $catalogRepository
     * @param ValidatorInterface $validator
     * @param CatalogTransformer $catalogTransformer
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        CatalogRepository $catalogRepository,
        ValidatorInterface $validator,
        CatalogTransformer $catalogTransformer
    )
    {
        parent::__construct($dm, $requestStack, $validator);
        $this->catalogRepository  = $catalogRepository;
        $this->catalogTransformer = $catalogTransformer;
    }

    public function setPostedObject()
    {
        $this->postedCatalog = $this->catalogTransformer->toObject($this->body);
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $this->validateDocument($this->postedCatalog);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        if ($this->postedCatalog->getParent()) {
            $this->setParent($this->postedCatalog, $this->postedCatalog->getParent()->getId());
        }
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($this->postedCatalog);
        $this->dm->flush();

        $this->apiResponse->setData($this->catalogTransformer->toArray($this->postedCatalog));
        return $this->apiResponse;

    }

    public function edit(string $id)
    {
        if (!$catalog = $this->catalogRepository->getCatalogById($id)) {
            $this->apiResponse->addError(Errors::CATALOG_NOT_FOUND);
            return $this->apiResponse;
        }

        $catalog->setName($this->postedCatalog->getName() ?? $catalog->getName());
        $catalog->setDescription($this->postedCatalog->getDescription() ?? $catalog->getDescription());

        if ($this->postedCatalog->getParent()) {
            $this->setParent($catalog, $this->postedCatalog->getParent()->getId());
        }

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($catalog);
        $this->dm->flush();

        $this->apiResponse->setData($this->catalogTransformer->toArray($catalog));
        return $this->apiResponse;
    }

    /**
     * @param string $id
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function delete(string $id)
    {
        if (!$catalog = $this->catalogRepository->getCatalogById($id)) {
            $this->apiResponse->addError(Errors::CATALOG_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->dm->remove($catalog);
        $this->dm->flush();

        return $this->apiResponse;
    }

    /**
     * @param Catalog     $catalog
     * @param string|null $parentId
     * @return ApiResponse|void
     */
    private function setParent(Catalog $catalog, ?string $parentId)
    {
        if (!$parentId) {
            return;
        }

        if ($catalog->getId() === $parentId) {
            return;
        }

        if (!$newParent = $this->catalogRepository->getCatalogById($parentId)) {
            return $this->apiResponse->addError(Errors::CATALOG_PARENT_NOT_FOUND);
        }

        if ($oldParent = $catalog->getParent()) {
            $catalog->removeParent($oldParent);
        }

        $catalog->setParent($newParent);
    }
}