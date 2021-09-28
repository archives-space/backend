<?php

namespace App\DataTransformer\Catalog\Picture;

use App\DataTransformer\Catalog\BaseCatalogTransformer;
use App\Document\Catalog\Picture\Version;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class VersionTransformer extends BaseCatalogTransformer
{

    /**
     * @param      $object
     * @param bool $fullInfo
     * @return mixed
     * @throws ExceptionInterface
     */
    public function toArray($object, bool $fullInfo = true)
    {
        return $this->normalize($object);
    }

    /**
     * @param $array
     * @return Version
     * @throws ExceptionInterface
     */
    public function toObject($array): Version
    {
        if($isEdited = $array['license']['isEdited']??null){
            if(!is_bool($isEdited)){
                $array['license']['isEdited'] = filter_var($isEdited,FILTER_VALIDATE_BOOLEAN);
            }

        }

        return $this->denormalize($array, Version::class);
    }
}