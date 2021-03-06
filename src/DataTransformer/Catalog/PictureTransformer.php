<?php

namespace App\DataTransformer\Catalog;

use App\Document\Catalog\Picture;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PictureTransformer extends BaseCatalogTransformer
{
    /**
     * @param $object
     * @return mixed
     */
    public function toArray($object, bool $fullInfo = true)
    {
        $picture = $this->normalize($object);

        $picture['detail'] = $this->router->generate('PICTURE_DETAIL', [
            'id' => $object->getId(),
        ]);

        $picture['breadcrumbs'] = $fullInfo ? $this->getBreadcrumb($object) : null;

        return $picture;
    }

    /**
     * @param $array
     * @return Picture
     * @throws ExceptionInterface
     */
    public function toObject($array): Picture
    {
        return $this->denormalize($array, Picture::class);
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