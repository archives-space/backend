<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\PlaceTransformer;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\PlaceRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var PlaceTransformer
     */
    private $placeTransformer;

    public function __construct(
        DocumentManager $dm,
        RequestStack $requestStack,
        PlaceRepository $placeRepository,
        PlaceTransformer $placeTransformer,
        ValidatorInterface $validator
    )
    {
        parent::__construct($dm, $requestStack, $validator);
        $this->placeRepository     = $placeRepository;
        $this->placeTransformer    = $placeTransformer;
    }

    public function create()
    {
        $place = $this->placeTransformer->toObject($this->body);

        $this->validateDocument($place);

        if($this->apiResponse->isError()){
            return $this->apiResponse;
        }

        $this->dm->persist($place);
        $this->dm->flush();
        $this->apiResponse->setData($this->placeTransformer->toArray($place));
        return $this->apiResponse;
    }

    public function edit(string $id)
    {
        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
            return $this->apiResponse;
        }

        $placeUpdated = $this->placeTransformer->toObject($this->body);

        $place->setName($placeUpdated->getName() ?: $place->getName());
        $place->setDescription($placeUpdated->getDescription() ?: $place->getDescription());
        $place->setWikipedia($placeUpdated->getWikipedia() ?: $place->getWikipedia());
        $place->setPosition($placeUpdated->getPosition() ?: $place->getPosition());

        $this->dm->persist($place);
        $this->dm->flush();
        $this->apiResponse->setData($this->placeTransformer->toArray($place));
        return $this->apiResponse;
    }

    public function delete(string $id)
    {
        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
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