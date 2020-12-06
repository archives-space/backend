<?php

namespace App\Manager\Catalog;

use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Model\ApiResponse\Error;
use App\Repository\Catalog\CatalogRepository;
use App\ArrayGenerator\Catalog\CatalogArrayGenerator;
use App\Repository\Catalog\PlaceRepository;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class PlaceManager extends BaseManager
{
    const BODY_PARAM_NAME        = 'name';
    const BODY_PARAM_DESCRIPTION = 'description';
    const BODY_PARAM_WIKIPEDIA   = 'wikipedia';
    const BODY_PARAM_POSITION    = 'position';
    const BODY_PARAM_CREATEDAT   = 'createdat';
    const BODY_PARAM_UPDATEDAT   = 'updatedat';

    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PlaceRepository $placeRepository
    )
    {
        parent::__construct($dm, $requestStack);
        $this->placeRepository = $placeRepository;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function edit(string $id)
    {
        // TODO: Implement edit() method.
    }

    public function delete(string $id)
    {
        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(ErrorCodes::PLACE_NOT_FOUND);
            return $this->apiResponse;
        }

        foreach ($place->getPictures() as $picture) {
            $place->removePicture($picture);
        }

        $this->dm->remove($place);
        $this->dm->flush();

        return (new ApiResponse([]));
    }

    public function requiredField()
    {
        return [
            self::BODY_PARAM_NAME,
        ];
    }

    public function setFields()
    {
        $this->name        = $this->body[self::BODY_PARAM_NAME] ?? null;
        $this->description = $this->body[self::BODY_PARAM_DESCRIPTION] ?? null;
        $this->wikipedia   = $this->body[self::BODY_PARAM_WIKIPEDIA] ?? null;
        $this->position    = $this->body[self::BODY_PARAM_POSITION] ?? null;
        $this->createdat   = $this->body[self::BODY_PARAM_CREATEDAT] ?? null;
        $this->updatedat   = $this->body[self::BODY_PARAM_UPDATEDAT] ?? null;
    }
}