<?php

namespace App\ArrayGenerator\Catalog;

use App\Document\Catalog\Picture;
use App\Document\Catalog\Place;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PlaceArrayGenerator extends BaseCatalogToArray
{
    /**
     * @param Place $object
     * @param bool  $fullInfo
     * @return array
     */
    public function toArray($object, bool $fullInfo = true): array
    {
        return [
            'id'          => $object->getId(),
            'name'        => $object->getName(),
            'description' => $object->getDescription(),
            'wikipedia'   => $object->getWikipedia(),
            'position'    => [
                'lat' => $object->getPosition() ? $object->getPosition()->getLat() : null,
                'lng' => $object->getPosition() ? $object->getPosition()->getLng() : null,
            ],
            'createdat'   => $object->getCreatedAt(),
            'updatedat'   => $object->getUpdatedAt(),
            'pictures'    => $this->getPictures($object, $fullInfo),
            'detail'      => $this->router->generate('PLACE_DETAIL', [
                'id' => $object->getId(),
            ]),
        ];
    }

    /**
     * @param Place $place
     * @param bool  $fullInfo
     * @return array|array[]|null
     */
    private function getPictures(Place $place, bool $fullInfo = true)
    {
        return array_map(function (Picture $picture) {
            return [
                'id'     => $picture->getId(),
                'name'   => $picture->getName(),
                'detail' => $this->router->generate('PICTURE_DETAIL', [
                    'id' => $picture->getId(),
                ]),
            ];
        }, $place->getPictures()->toArray());

//        if (!$fullInfo) {
//            return count($place->getPictures()->toArray());
//        }
//
//        return array_map(function (Picture $picture) {
//            return $this->pictureArrayGenerator->toArray($picture, false);
//        }, $place->getPictures()->toArray());
    }
}