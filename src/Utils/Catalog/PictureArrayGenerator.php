<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Picture;
use App\Document\Catalog\Resolution;
use App\Document\User\User;
use Symfony\Component\Routing\RouterInterface;

class PictureArrayGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CatalogArrayGenerator
     */
    private $catalogArrayGenerator;

    /**
     * UserArrayGenerator constructor.
     * @param RouterInterface       $router
     * @param CatalogArrayGenerator $catalogArrayGenerator
     */
    public function __construct(
        RouterInterface $router,
        CatalogArrayGenerator $catalogArrayGenerator
    )
    {
        $this->router                = $router;
        $this->catalogArrayGenerator = $catalogArrayGenerator;
    }

    /**
     * @param Picture $picture
     * @param bool    $fullInfo
     * @return array
     */
    public function toArray(Picture $picture, $fullInfo = true): array
    {
        return [
            'id'               => $picture->getId(),
            //            'catalogId'          => $user->getCatalogId(),
            //            'placeId'          => $user->getPlaceId(),
            'name'             => $picture->getName(),
            'description'      => $picture->getDescription(),
            'source'           => $picture->getSource(),
            'edited'           => $picture->isEdited(),
            'originalFileName' => $picture->getOriginalFileName(),
            'typeMime'         => $picture->getTypeMime(),
            'hash'             => $picture->getHash(),
            'takenAt'          => $picture->getTakenAt(),
            'createdAt'        => $picture->getCreatedAt(),
            'updatedAt'        => $picture->getUpdatedAt(),
            'exif'             => [
                'id'           => $picture->getExif() ? $picture->getExif()->getId() : null,
                'model'        => $picture->getExif() ? $picture->getExif()->getModel() : null,
                'manufacturer' => $picture->getExif() ? $picture->getExif()->getManufacturer() : null,
                'aperture'     => $picture->getExif() ? $picture->getExif()->getAperture() : null,
                'iso'          => $picture->getExif() ? $picture->getExif()->getIso() : null,
                'exposure'     => $picture->getExif() ? $picture->getExif()->getExposure() : null,
                'focalLength'  => $picture->getExif() ? $picture->getExif()->getFocalLength() : null,
                'flash'        => $picture->getExif() ? $picture->getExif()->getFlash() : null,
            ],
            'resolutions'      => $this->getResolutions($picture),
            'position'         => [
                'lat' => $picture->getPosition() ? $picture->getPosition()->getLat() : null,
                'lng' => $picture->getPosition() ? $picture->getPosition()->getLng() : null,
            ],
            'catalog'          => $picture->getCatalog() ? $this->catalogArrayGenerator->toArray($picture->getCatalog(), $fullInfo) : null,
            'pictureDetail'    => $this->router->generate('PICTURE_DETAIL', [
                'id' => $picture->getId(),
            ]),
        ];
    }

    private function getResolutions(Picture $picture)
    {
        return array_map(function (Resolution $resolution) {
            return [
                'id'        => $resolution->getId(),
                //                'file'      => $resolution->getFile(),
                'width'     => $resolution->getWidth(),
                'height'    => $resolution->getHeight(),
                'size'      => $resolution->getSize(),
                'sizeLabel' => $resolution->getSizeLabel(),
                //                'key'       => $resolution->getKey(),
            ];
        }, $picture->getResolutions()->toArray());
    }
}