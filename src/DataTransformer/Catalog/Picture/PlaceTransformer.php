<?php

namespace App\DataTransformer\Catalog\Picture;

use App\DataTransformer\Catalog\BaseCatalogTransformer;
use App\Document\Catalog\Picture\Place;
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
        if($lat = $array['position']['lat']??null){
            if(!is_float($lat)){
                $array['position']['lat'] = filter_var($lat,FILTER_VALIDATE_FLOAT);
            }
        }
        if($lng = $array['position']['lng']??null){
            if(!is_float($lng)){
                $array['position']['lng'] = filter_var($lng,FILTER_VALIDATE_FLOAT);
            }
        }
        return $this->denormalize($array, Place::class);
    }
}