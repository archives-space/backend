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
     * UserArrayGenerator constructor.
     * @param RouterInterface $router
     */
    public function __construct(
        RouterInterface $router
    )
    {
        $this->router = $router;
    }

    /**
     * @param Picture $picture
     * @return array
     */
    public function toArray(Picture $picture): array
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
            'checksum'         => $picture->getChecksum(),
            'hash'             => $picture->getHash(),
            'takenAt'          => $picture->getTakenAt(),
            'createdAt'        => $picture->getCreatedAt(),
            'updatedAt'        => $picture->getUpdatedAt(),
            'exif'             => [
                'id'           => $picture->getExif()->getId(),
                'model'        => $picture->getExif()->getModel(),
                'manufacturer' => $picture->getExif()->getManufacturer(),
                'aperture'     => $picture->getExif()->getAperture(),
                'iso'          => $picture->getExif()->getIso(),
                'exposure'     => $picture->getExif()->getExposure(),
                'focalLength'  => $picture->getExif()->getFocalLength(),
                'flash'        => $picture->getExif()->getFlash(),
            ],
            'resolutions'      => $this->getResolutions($picture),
            'position'         => [
                'lat' => $picture->getPosition() ? $picture->getPosition()->getLat() : null,
                'lng' => $picture->getPosition() ? $picture->getPosition()->getLng() : null,
            ],
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