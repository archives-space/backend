<?php

namespace App\Controller\Catalog;

use App\Manager\Catalog\CatalogManager;
use App\Provider\Catalog\CatalogProvider;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CatalogController
 * @package App\Controller\Catalog
 * @Route(defaults={"_format"="json"})
 */
class CatalogController extends AbstractController
{
    /**
     * @var CatalogManager
     */
    private $catalogManager;

    /**
     * @var CatalogProvider
     */
    private $catalogProvider;

    /**
     * CatalogController constructor.
     * @param CatalogManager  $catalogManager
     * @param CatalogProvider $catalogProvider
     */
    public function __construct(
        CatalogManager $catalogManager,
        CatalogProvider $catalogProvider
    )
    {
        $this->catalogManager  = $catalogManager;
        $this->catalogProvider = $catalogProvider;
    }

    /**
     * @Route("/catalogs", name="CATALOG_CREATE", methods="POST")
     * @return Response
     * @throws MongoDBException
     */
    public function catalogCreate(): Response
    {
        return $this->catalogManager->init()->create()->getResponse();
    }

    /**
     * Création de la route "CATALOGS"
     * @Route("/catalogs", name="CATALOGS", methods={"GET"})
     * @return Response
     * @throws MongoDBException
     */
    public function catalogs()
    {
        return $this->catalogProvider->getCatalogs()->getResponse();
    }

    /**
     * Création de la route "CATALOG DETAIL"
     * @Route("/catalogs/{id}", name="CATALOG_DETAIL", methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function catalogDetail(string $id)
    {
        return $this->catalogProvider->getCatalogById($id)->getResponse();
    }

    /**
     * Création de la route "CATALOG_EDIT"
     * @Route("/catalogs/{id}", name="CATALOG_EDIT", methods={"PUT"})
     * @return Response
     */
    public function catalogEdit(string $id)
    {
        return $this->catalogManager->init()->edit($id)->getResponse();
    }

    /**
     * Création de la route "CATALOG_DELETE"
     * @Route("/catalogs/{id}", name="CATALOG_DELETE", methods={"DELETE"})
     * @param string $id
     * @return Response
     */
    public function catalogDelete(string $id)
    {
        return $this->catalogManager->delete($id)->getResponse();
    }
}
