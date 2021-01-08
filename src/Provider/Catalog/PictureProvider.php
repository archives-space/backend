<?php

namespace App\Provider\Catalog;

use App\Document\Catalog\Picture;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\PictureRepository;
use App\ArrayGenerator\Catalog\PictureArrayGenerator;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;

class PictureProvider extends BaseProvider
{
    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var PictureArrayGenerator
     */
    private $pictureArrayGenerator;

    public function __construct(
        RequestStack $requestStack,
        PictureRepository $pictureRepository,
        PictureArrayGenerator $pictureArrayGenerator
    )
    {
        parent::__construct($requestStack);
        $this->pictureRepository     = $pictureRepository;
        $this->pictureArrayGenerator = $pictureArrayGenerator;
    }

    public function findById(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            return (new ApiResponse(null, Errors::PICTURE_NOT_FOUND));
        }

        $this->apiResponse->setData($this->pictureArrayGenerator->toArray($picture))->setNbTotalData(1);
        return $this->apiResponse;
    }

    public function findAll()
    {
        $data      = $this->pictureRepository->getAllPicturesPaginate($this->nbPerPage, $this->page);
        $cataloges = array_map(function (Picture $picture) {
            return $this->pictureArrayGenerator->toArray($picture, false);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($cataloges)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}