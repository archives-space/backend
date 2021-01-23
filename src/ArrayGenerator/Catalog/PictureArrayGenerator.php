<?php

namespace App\ArrayGenerator\Catalog;

use App\Document\Catalog\Picture;
use App\Document\Catalog\Resolution;

class PictureArrayGenerator extends BaseCatalogToArray
{
    /**
     * @param Picture $object
     * @param bool    $fullInfo
     * @return array
     */
    public function toArray($object, bool $fullInfo = true): array
    {

        $picture = $this->serialize($object);

        $picture['detail'] = $this->router->generate('PICTURE_DETAIL', [
            'id' => $object->getId(),
        ]);

        $picture['breadcrumbs'] = $fullInfo ? $this->getBreadcrumb($object) : null;

        return $picture;


        return [
            'id'               => $object->getId(),
            //            'placeId'          => $user->getPlaceId(),
            'name'             => $object->getName(),
            'description'      => $object->getDescription(),
            'source'           => $object->getSource(),
            'edited'           => $object->isEdited(),
            'originalFileName' => $object->getOriginalFileName(),
            'typeMime'         => $object->getTypeMime(),
            'hash'             => $object->getHash(),
            'takenAt'          => $object->getTakenAt(),
            'createdAt'        => $object->getCreatedAt(),
            'updatedAt'        => $object->getUpdatedAt(),
            'exif'             => $this->exifToArray($object),
            'resolutions'      => $this->getResolutions($object),
            'position'         => [
                'lat' => $object->getPosition() ? $object->getPosition()->getLat() : null,
                'lng' => $object->getPosition() ? $object->getPosition()->getLng() : null,
            ],
            'license'          => [
                'name'     => $object->getLicense() ? $object->getLicense()->getName() : null,
                'isEdited' => $object->getLicense() ? $object->getLicense()->isEdited() : null,
            ],
            'catalog'          => $this->getCatalog($object),
            'place'            => $this->getPlace($object),
            'breadcrumb'       => $this->getBreadcrumb($object, $fullInfo),
            'detail'    => $this->router->generate('PICTURE_DETAIL', [
                'id' => $object->getId(),
            ]),
        ];
    }

    /**
     * @param Picture $picture
     * @return array
     */
    private function exifToArray(Picture $picture)
    {
        return [
            'id'           => $picture->getExif() ? $picture->getExif()->getId() : null,
            'model'        => $picture->getExif() ? $picture->getExif()->getModel() : null,
            'manufacturer' => $picture->getExif() ? $picture->getExif()->getManufacturer() : null,
            'aperture'     => $picture->getExif() ? $picture->getExif()->getAperture() : null,
            'iso'          => $picture->getExif() ? $picture->getExif()->getIso() : null,
            'exposure'     => $picture->getExif() ? $picture->getExif()->getExposure() : null,
            'focalLength'  => $picture->getExif() ? $picture->getExif()->getFocalLength() : null,
            'flash'        => $picture->getExif() ? $picture->getExif()->getFlash() : null,
        ];
    }

    /**
     * @param Picture $picture
     * @return array[]
     */
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

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getCatalog(Picture $picture)
    {
        if (!$catalog = $picture->getCatalog()) {
            return null;
        }
        return [
            'id'            => $catalog->getId(),
            'name'          => $catalog->getName(),
            'detail' => $this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]),
        ];
    }

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getPlace(Picture $picture)
    {
        if (!$place = $picture->getPlace()) {
            return null;
        }
        return [
            'id'            => $place->getId(),
            'name'          => $place->getName(),
            'detail' => $this->router->generate('PLACE_DETAIL', [
                'id' => $place->getId(),
            ]),
        ];
    }

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getBreadcrumb(Picture $picture)
    {
        if (!$catalog = $picture->getCatalog()) {
            return null;
        }
        return $this->catalogHelpers->getBreadCrumbs($catalog)->toArray();
    }
}