<?php

namespace App\Controller\Catalog;

use App\Manager\Catalog\CatalogManager;
use App\Provider\Catalog\CatalogProvider;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
    private CatalogManager $catalogManager;

    /**
     * @var CatalogProvider
     */
    private CatalogProvider $catalogProvider;

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
     * @Route("/catalogs", name="CATALOGS", methods={"GET"})
     * @return Response
     * @throws MongoDBException|ExceptionInterface
     */
    public function catalogs(): Response
    {
        return $this->catalogProvider->init()->findAll()->getResponse();
    }

    /**
     * @Route("/catalogs/root", name="CATALOG_ROOT", methods={"GET"})
     * @return Response
     * @throws ExceptionInterface
     */
    public function catalogRoot(): Response
    {
        return $this->catalogProvider->getRoot()->getResponse();
    }

    /**
     * @Route("/catalogs/{id}", name="CATALOG_DETAIL", methods={"GET"})
     * @param string $id
     * @return Response
     * @throws ExceptionInterface
     */
    public function catalogDetail(string $id): Response
    {
        return $this->catalogProvider->findById($id)->getResponse();
    }

    /**
     * @Route("/catalogs/{id}", name="CATALOG_EDIT", methods={"PUT"})
     * @param string $id
     * @return Response
     */
    public function catalogEdit(string $id): Response
    {
        return $this->catalogManager->init()->edit($id)->getResponse();
    }

    /**
     * @Route("/catalogs/{id}", name="CATALOG_DELETE", methods={"DELETE"})
     * @param string $id
     * @return Response
     * @throws MongoDBException
     */
    public function catalogDelete(string $id): Response
    {
        return $this->catalogManager->delete($id)->getResponse();
    }
}
