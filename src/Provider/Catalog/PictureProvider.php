<?php

namespace App\Provider\Catalog;

use App\DataTransformer\Catalog\PictureTransformer;
use App\Document\Catalog\Picture;
use App\Model\ApiResponse\ApiResponse;
use App\Provider\BaseProvider;
use App\Repository\Catalog\PictureRepository;
use App\Utils\Response\Errors;
use Symfony\Component\HttpFoundation\RequestStack;

class PictureProvider extends BaseProvider
{
    /**
     * @var PictureRepository
     */
    private $pictureRepository;

    /**
     * @var PictureTransformer
     */
    private $pictureTransformer;

    public function __construct(
        RequestStack $requestStack,
        PictureRepository $pictureRepository,
        PictureTransformer $pictureTransformer
    )
    {
        parent::__construct($requestStack);
        $this->pictureRepository     = $pictureRepository;
        $this->pictureTransformer = $pictureTransformer;
    }

    public function findById(string $id)
    {
        if (!$picture = $this->pictureRepository->getPictureById($id)) {
            $this->apiResponse->addError(Errors::PICTURE_NOT_FOUND);
            return $this->apiResponse;
        }

        $this->apiResponse->setData($this->pictureTransformer->toArray($picture))->setNbTotalData(1);
        return $this->apiResponse;
    }

    public function findAll()
    {
        $data      = $this->pictureRepository->getAllPicturesPaginate($this->nbPerPage, $this->page);

        $cataloges = array_map(function (Picture $picture) {
            return $this->pictureTransformer->toArray($picture, false);
        }, $data[BaseProvider::RESULT]->toArray());

        $this->apiResponse->setData($cataloges)->setNbTotalData($data[BaseProvider::NB_TOTAL_RESULT]);
        return $this->apiResponse;
    }
}