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
    const BODY_PARAM_NAME        = 'name';
    const BODY_PARAM_DESCRIPTION = 'description';
    const BODY_PARAM_PARENTID    = 'parentId';

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var CatalogTransformer
     */
    private $catalogTransformer;

    /**
     * PictureManager constructor.
     * @param DocumentManager       $dm
     * @param RequestStack          $requestStack
     * @param CatalogRepository     $catalogRepository
     * @param ValidatorInterface    $validator
     * @param CatalogTransformer    $catalogTransformer
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
        $this->catalogRepository     = $catalogRepository;
        $this->catalogTransformer    = $catalogTransformer;
    }

    public function setFields()
    {
        $this->name        = $this->body[self::BODY_PARAM_NAME] ?? null;
        $this->description = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $this->parentId    = $this->body[self::BODY_PARAM_PARENTID] ?? null;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $catalog = $this->catalogTransformer->toObject($this->body);

        $this->validateDocument($catalog);

        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->setParent($catalog, $this->parentId);
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($catalog);
        $this->dm->flush();

        $this->apiResponse->setData($this->catalogTransformer->toArray($catalog));
        return $this->apiResponse;

    }

    public function edit(string $id)
    {
        if (!$catalog = $this->catalogRepository->getCatalogById($id)) {
            return (new ApiResponse(null, Errors::CATALOG_NOT_FOUND));
        }

        $catalogUpdated = $this->catalogTransformer->toObject($this->body);

        $catalog->setName($catalogUpdated->getName() ?? $catalog->getName());
        $catalog->setDescription($catalogUpdated->getDescription() ?? $catalog->getDescription());

        $this->setParent($catalog, $this->parentId);

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
            return (new ApiResponse(null, Errors::CATALOG_NOT_FOUND));
        }

        $this->dm->remove($catalog);
        $this->dm->flush();

        return (new ApiResponse([]));
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

    /**
     * @return string[]
     */
    public function requiredField()
    {
        return [
            self::BODY_PARAM_NAME,
        ];
    }
}