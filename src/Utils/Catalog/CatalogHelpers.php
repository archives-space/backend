<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\Breadcrumb\Breadcrumb;
use App\Model\Breadcrumb\BreadcrumbsLink;
use App\Repository\Catalog\CatalogRepository;
use Symfony\Component\Routing\RouterInterface;

class CatalogHelpers
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CatalogRepository
     */
    private $catalogRepository;

    /**
     * @var array
     */
    private $idsCatalog;

    /**
     * @var Breadcrumb
     */
    private $breadcrumb;


    /**
     * CatalogHelpers constructor.
     * @param RouterInterface   $router
     * @param CatalogRepository $catalogRepository
     */
    public function __construct(
        RouterInterface $router,
        CatalogRepository $catalogRepository
    )
    {
        $this->router            = $router;
        $this->catalogRepository = $catalogRepository;
        $this->idsCatalog        = [];
        $this->breadcrumb        = new Breadcrumb();
    }

    /**
     * @param Catalog $catalog
     * @return Breadcrumb
     */
    public function getBreadCrumbs(Catalog $catalog)
    {
        $this->idsCatalog[] = $catalog->getId();
        $this->breadcrumb->addLink((new BreadcrumbsLink())
            ->setId($catalog->getId())
            ->setTitle($catalog->getName())
            ->setUrl($this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]))
            ->setIsActual(true));

        $this->addBreadCrumbsLink($catalog->getParent());

        return $this->breadcrumb;
    }

    /**
     * @param Catalog|null $catalog
     */
    private function addBreadCrumbsLink(?Catalog $catalog)
    {
        if (null !== $catalog && !in_array($catalog->getId(), $this->idsCatalog)) {
            $this->idsCatalog[] = $catalog->getId();
            $this->breadcrumb->addLink((new BreadcrumbsLink())
                ->setId($catalog->getId())
                ->setTitle($catalog->getName())
                ->setUrl($this->router->generate('CATALOG_DETAIL', [
                    'id' => $catalog->getId(),
                ])));
            $this->addBreadCrumbsLink($catalog->getParent());
        }
    }
}