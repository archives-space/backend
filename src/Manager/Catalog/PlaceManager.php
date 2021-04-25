<?php

namespace App\Manager\Catalog;

use App\DataTransformer\Catalog\Picture\PlaceTransformer;
use App\Document\Catalog\Picture\Place;
use App\Model\ApiResponse\ApiResponse;
use App\Manager\BaseManager;
use App\Repository\Catalog\Picture\PlaceRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlaceManager extends BaseManager
{
    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var PlaceTransformer
     */
    private $placeTransformer;

    /**
     * @var Place
     */
    private $postedPlace;

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


    public function setPostedObject()
    {
        $this->postedPlace    = $this->placeTransformer->toObject($this->body);
    }

    public function create()
    {
        $this->validateDocument($this->postedPlace);

        if($this->apiResponse->isError()){
            return $this->apiResponse;
        }

        $this->dm->persist($this->postedPlace);
        $this->dm->flush();
        $this->apiResponse->setData($this->placeTransformer->toArray($this->postedPlace));
        return $this->apiResponse;
    }

    public function edit(string $id)
    {
        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
            return $this->apiResponse;
        }

        $place->setName($this->postedPlace->getName() ?: $place->getName());
        $place->setDescription($this->postedPlace->getDescription() ?: $place->getDescription());
        $place->setWikidata($this->postedPlace->getWikidata() ?: $place->getWikidata());
        $place->setPosition($this->postedPlace->getPosition() ?: $place->getPosition());

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

        return $this->apiResponse;
    }
}