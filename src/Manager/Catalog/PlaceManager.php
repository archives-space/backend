<?php

namespace App\Manager\Catalog;

use App\ArrayGenerator\Catalog\PlaceArrayGenerator;
use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Place;
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

    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var PlaceArrayGenerator
     */
    private $placeArrayGenerator;

    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PlaceRepository $placeRepository,
        PlaceArrayGenerator $placeArrayGenerator
    )
    {
        parent::__construct($dm, $requestStack);
        $this->placeRepository     = $placeRepository;
        $this->placeArrayGenerator = $placeArrayGenerator;
    }

    public function create()
    {
        $this->checkMissedField();
        if ($this->apiResponse->isError()) {
            return $this->apiResponse;
        }

        $place = new Place();

        $place->setName($this->name);
        $place->setDescription($this->description);
        $place->setWikipedia($this->wikipedia);
        $place->setPosition($this->position);

        $this->dm->persist($place);
        $this->dm->flush();
        $this->apiResponse->setData($this->placeArrayGenerator->toArray($place));
        return $this->apiResponse;
    }

    public function edit(string $id)
    {
        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(ErrorCodes::PLACE_NOT_FOUND);
            return $this->apiResponse;
        }

        $place->setName($this->name ?: $place->getName());
        $place->setDescription($this->description ?: $place->getDescription());
        $place->setWikipedia($this->wikipedia ?: $place->getWikipedia());
        $place->setPosition($this->position ?: $place->getPosition());

        $this->dm->persist($place);
        $this->dm->flush();
        $this->apiResponse->setData($this->placeArrayGenerator->toArray($place));
        return $this->apiResponse;
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
    }
}