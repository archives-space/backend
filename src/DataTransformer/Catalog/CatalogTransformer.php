<?php

namespace App\DataTransformer\Catalog;

use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CatalogTransformer extends BaseCatalogTransformer
{
    /**
     * @param Catalog $object
     * @param bool    $fullInfo
     * @return mixed
     * @throws ExceptionInterface
     */
    public function toArray($object, bool $fullInfo = true)
    {
        return [
            'id'             => $object->getId(),
            'slug'           => $object->getSlug(),
            'name'           => $object->getName(),
            'description'    => $object->getDescription(),
            'parent'         => $object->getParent() ? $object->getParent()->getId() : null,
            'childrens'      => array_map(function (Catalog $child) {
                return ['id' => $child->getId()];
            }, $object->getChildrens()->toArray()),
            'createdAt'      => $object->getCreatedAt(),
            'updatedAt'      => $object->getUpdatedAt(),
            'pictures'       => array_map(function (Picture $picture) {
                return ['id' => $picture->getId()];
            }, $object->getPictures()->toArray()),
            'primaryPicture' => $object->getPrimaryPicture() ? $object->getPrimaryPicture()->getId() : $object->getPrimaryPicture(),
            'detail'         => $this->router->generate('CATALOG_DETAIL', [
                'id' => $object->getId(),
            ]),
            'breadcrumbs'    => $fullInfo ? $this->getFormattedBreadcrumbs($object) : null,
        ];
    }

    /**
     * @param $array
     * @return Catalog
     * @throws ExceptionInterface
     */
    public function toObject($array): Catalog
    {
        return $this->denormalize($array, Catalog::class);
    }

    /**
     * @param Catalog $catalog
     * @return array[]|null
     */
    private function getFormattedBreadcrumbs(Catalog $catalog): ?array
    {
        return $this->catalogHelpers->getBreadCrumbs($catalog)->toArray();
    }
}