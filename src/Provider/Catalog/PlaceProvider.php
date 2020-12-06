<?php

namespace App\Provider\Catalog;

use App\Document\Catalog\Place;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\PlaceRepository;
use App\ArrayGenerator\Catalog\PlaceArrayGenerator;
use App\Utils\Response\ErrorCodes;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class PlaceProvider extends BaseProvider
{
    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var PlaceArrayGenerator
     */
    private $placeArrayGenerator;

    public function __construct(
        RequestStack $requestStack,
        PlaceRepository $placeRepository,
        PlaceArrayGenerator $placeArrayGenerator
    )
    {
        parent::__construct($requestStack);
        $this->placeRepository     = $placeRepository;
        $this->placeArrayGenerator = $placeArrayGenerator;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id)
    {

        if (!$place = $this->placeRepository->getPlaceById($id)) {
            return (new ApiResponse(null, ErrorCodes::PLACE_NOT_FOUND));
        }

        $this->apiResponse->setData($this->placeArrayGenerator->toArray($place))->setNbTotalData(1);
        return $this->apiResponse;
    }

    /**
     * @return ApiResponse
     * @throws MongoDBException
     */
    public function findAll()
    {
        $data   = $this->placeRepository->getAllPlacesPaginate($this->nbPerPage, $this->page);
        $places = array_map(function (Place $place) {
            return $this->placeArrayGenerator->toArray($place, false);
        }, $data[BaseProvider::RESULT]->toArray());
        $this->apiResponse->setData($places)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}