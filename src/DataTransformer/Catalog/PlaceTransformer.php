<?php

namespace App\DataTransformer\Catalog;

use App\Document\Catalog\Place;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PlaceTransformer extends BaseCatalogTransformer
{

    /**
     * @param $object
     * @return mixed
     */
    public function toArray($object, bool $fullInfo = true)
    {
        $place = $this->normalize($object);

        $place['detail'] = $this->router->generate('PICTURE_DETAIL', [
            'id' => $object->getId(),
        ]);

        return $place;
    }

    /**
     * @param $array
     * @return Place
     * @throws ExceptionInterface
     */
    public function toObject($array): Place
    {
        return $this->denormalize($array, Place::class);
    }
}