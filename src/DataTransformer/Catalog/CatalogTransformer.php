<?php

namespace App\DataTransformer\Catalog;

use App\Document\Catalog\Catalog;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CatalogTransformer extends BaseCatalogTransformer
{
    /**
     * @param $object
     * @return mixed
     */
    public function toArray($object, bool $fullInfo = true)
    {
        $catalog = $this->normalize($object);

        $catalog['detail'] = $this->router->generate('CATALOG_DETAIL', [
            'id' => $object->getId(),
        ]);

        $catalog['breadcrumbs'] = $fullInfo ? $this->getFormatedBreadcrumbs($object) : null;

        return $catalog;
    }

    /**
     * @param $array
     * @return Catalog
     * @throws ExceptionInterface
     */
    public function toObject($array):Catalog
    {
        return $this->denormalize($array, Catalog::class);
    }

    /**
     * @param Catalog $catalog
     * @return array[]|null
     */
    private function getFormatedBreadcrumbs(Catalog $catalog)
    {
        return $this->catalogHelpers->getBreadCrumbs($catalog)->toArray();
    }
}