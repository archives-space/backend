<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture;
use App\Model\Catalog\BreadcrumbsLink;
use Symfony\Component\Routing\RouterInterface;

class CatalogArrayGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CatalogHelpers
     */
    private $catalogHelpers;

    /**
     * UserArrayGenerator constructor.
     * @param RouterInterface       $router
     * @param CatalogHelpers        $catalogHelpers
     */
    public function __construct(
        RouterInterface $router,
        CatalogHelpers $catalogHelpers
    )
    {
        $this->router                = $router;
        $this->catalogHelpers        = $catalogHelpers;
    }

    /**
     * @param Catalog $catalog
     * @param bool    $fullInfo
     * @return array
     */
    public function toArray(Catalog $catalog, $fullInfo = true): array
    {
        return [
            'id'            => $catalog->getId(),
            'name'          => $catalog->getName(),
            'description'   => $catalog->getDescription(),
            'createdAt'     => $catalog->getCreatedAt(),
            'updatedAt'     => $catalog->getUpdatedAt(),
            'parent'        => $catalog->getParent() ? $catalog->getParent()->getId() : null,
            'childrens'     => array_map(function (Catalog $catalog) {
                return $this->getCatalogSmall($catalog);
            }, $catalog->getChildrens()->toArray()),
            'catalogDetail' => $this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]),
            'breadcrumbs'   => $this->getFormatedBreadcrumbs($catalog, $fullInfo),
        ];
    }

    /**
     * @param Catalog $catalog
     * @return array
     */
    private function getCatalogSmall(Catalog $catalog)
    {
        return [
            'id'            => $catalog->getId(),
            'name'          => $catalog->getName(),
            'description'   => $catalog->getDescription(),
            'createdAt'     => $catalog->getCreatedAt(),
            'updatedAt'     => $catalog->getUpdatedAt(),
            'catalogDetail' => $this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]),
        ];
    }

    /**
     * @param Catalog $catalog
     * @param bool    $fullInfo
     * @return array[]|null
     */
    private function getFormatedBreadcrumbs(Catalog $catalog, $fullInfo)
    {
        if (!$fullInfo) {
            return null;
        }
        return array_map(function (BreadcrumbsLink $breadcrumbsLink) {
            return [
                'id'       => $breadcrumbsLink->getId(),
                'title'    => $breadcrumbsLink->getTitle(),
                'url'      => $breadcrumbsLink->getUrl(),
                'isActual' => $breadcrumbsLink->isActual(),
            ];
        }, $this->catalogHelpers->getBreadCrumbs($catalog));
    }
}