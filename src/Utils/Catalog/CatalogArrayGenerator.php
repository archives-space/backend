<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Catalog;
use Symfony\Component\Routing\RouterInterface;

class CatalogArrayGenerator
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
     * @param Catalog $catalog
     * @return array
     */
    public function toArray(Catalog $catalog): array
    {
        return [
            'id'          => $catalog->getId(),
            'name'        => $catalog->getName(),
            'description' => $catalog->getDescription(),
            'createdAt'   => $catalog->getCreatedAt(),
            'updatedAt'   => $catalog->getUpdatedAt(),
            'parent'      => $catalog->getParent() ? $catalog->getParent()->getId():null,
            'childrens'   => array_map(function (Catalog $catalog) {
                return $catalog->getId();
            }, $catalog->getChildrens()->toArray()),
            //            'catalogDetail' => $this->router->generate('PICTURE_DETAIL', [
            //                'id' => $picture->getId(),
            //            ]),
        ];
    }
}