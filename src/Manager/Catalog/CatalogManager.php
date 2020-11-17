<?php

namespace App\Manager\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Model\ApiResponse\Error;
use App\Repository\Catalog\CatalogRepository;
use App\Utils\Catalog\CatalogArrayGenerator;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var CatalogArrayGenerator
     */
    private $catalogArrayGenerator;

    /**
     * PictureManager constructor.
     * @param DocumentManager       $dm
     * @param RequestStack          $requestStack
     * @param CatalogRepository     $catalogRepository
     * @param CatalogArrayGenerator $catalogArrayGenerator
     */
    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        CatalogRepository $catalogRepository,
        CatalogArrayGenerator $catalogArrayGenerator
    )
    {
        parent::__construct($dm, $requestStack);
        $this->catalogRepository     = $catalogRepository;
        $this->catalogArrayGenerator = $catalogArrayGenerator;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function create()
    {
        $this->checkMissedField();
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }
        $name        = $this->body[self::BODY_PARAM_NAME];
        $description = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $parentId    = $this->body[self::BODY_PARAM_PARENTID] ?? null;

        $catalog = new Catalog();
        $catalog->setName($name);
        $catalog->setDescription($description);

        $this->setParent($catalog, $parentId);
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($catalog);
        $this->dm->flush();

        $this->apiResponse->setData($this->catalogArrayGenerator->toArray($catalog));
        return $this->apiResponse;

    }

    public function edit(string $id)
    {
        if (!$catalog = $this->catalogRepository->getCatalogById($id)) {
            return (new ApiResponse(null, ErrorCodes::NO_CATALOG));
        }

        $name        = $this->body[self::BODY_PARAM_NAME] ?? null;
        $description = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $parentId    = $this->body[self::BODY_PARAM_PARENTID] ?? null;

        $catalog->setName($name ?? $catalog->getName());
        $catalog->setDescription($description ?? $catalog->getDescription());

        $this->setParent($catalog, $parentId);
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $this->dm->persist($catalog);
        $this->dm->flush();

        $this->apiResponse->setData($this->catalogArrayGenerator->toArray($catalog));
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
            return (new ApiResponse(null, ErrorCodes::NO_CATALOG));
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
            return $this->apiResponse->addError(new Error(ErrorCodes::NO_PARENT));
        }

        if($oldParent = $catalog->getParent()){
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