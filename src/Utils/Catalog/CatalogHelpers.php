<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Catalog;
use App\Model\Catalog\BreadcrumbsLink;
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
     * @var array
     */
    private $breadcrumbs;


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
        $this->breadcrumbs       = [];
    }

    /**
     * @param Catalog $catalog
     * @return array
     */
    public function getBreadCrumbs(Catalog $catalog)
    {
        $this->idsCatalog[]  = $catalog->getId();
        $this->breadcrumbs[] = (new BreadcrumbsLink())
            ->setId($catalog->getId())
            ->setTitle($catalog->getName())
            ->setUrl($this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]))
            ->setIsActual(true)
        ;

        $this->addBreadCrumbsLink($catalog->getParent());

        return array_reverse($this->breadcrumbs);
    }

    /**
     * @param Catalog|null $catalog
     */
    private function addBreadCrumbsLink(?Catalog $catalog)
    {
        if (null !== $catalog && !in_array($catalog->getId(), $this->idsCatalog)) {
            $this->idsCatalog[]  = $catalog->getId();
            $this->breadcrumbs[] = (new BreadcrumbsLink())
                ->setId($catalog->getId())
                ->setTitle($catalog->getName())
                ->setUrl($this->router->generate('CATALOG_DETAIL', [
                    'id' => $catalog->getId(),
                ]))
            ;
            $this->addBreadCrumbsLink($catalog->getParent());
        }
    }
}