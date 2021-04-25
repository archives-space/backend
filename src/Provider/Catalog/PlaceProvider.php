<?php

namespace App\Provider\Catalog;

use App\DataTransformer\Catalog\Picture\PlaceTransformer;
use App\Document\Catalog\Picture\Place;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\Picture\PlaceRepository;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class PlaceProvider extends BaseProvider
{
    /**
     * @var PlaceRepository
     */
    private $placeRepository;

    /**
     * @var PlaceTransformer
     */
    private $placeTransformer;

    public function __construct(
        RequestStack $requestStack,
        PlaceRepository $placeRepository,
        PlaceTransformer $placeTransformer
    )
    {
        parent::__construct($requestStack);
        $this->placeRepository     = $placeRepository;
        $this->placeTransformer = $placeTransformer;
    }

    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id)
    {

        if (!$place = $this->placeRepository->getPlaceById($id)) {
            $this->apiResponse->addError(Errors::PLACE_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->apiResponse->setData($this->placeTransformer->toArray($place))->setNbTotalData(1);
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
            return $this->placeTransformer->toArray($place, false);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($places)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}