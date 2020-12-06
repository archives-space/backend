<?php

namespace App\Controller\Catalog;

use App\Manager\Catalog\PlaceManager;
use App\Provider\Catalog\PlaceProvider;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlaceController
 * @package App\Controller\Catalog
 * @Route(defaults={"_format"="json"})
 */
class PlaceController extends AbstractController
{

    /**
     * @var PlaceProvider
     */
    private $placeProvider;

    /**
     * @var PlaceManager
     */
    private $placeManager;

    public function __construct(
        PlaceProvider $placeProvider,
        PlaceManager $placeManager
    )
    {
        $this->placeProvider = $placeProvider;
        $this->placeManager  = $placeManager;
    }

    /**
     * @Route("/places", name="PLACE_CREATE", methods="POST")
     * @return Response
     */
    public function placeCreate(): Response
    {
        return $this->placeManager->init()->create()->getResponse();
    }

    /**
     * @Route("/places", name="PLACES", methods={"GET"})
     * @return Response
     * @throws MongoDBException
     */
    public function places()
    {
        return $this->placeProvider->findAll()->getResponse();
    }

    /**
     * @Route("/places/{id}", name="PLACE_DETAIL", methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function placeDetail(string $id)
    {
        return $this->placeProvider->findById($id)->getResponse();
    }

    /**
     * @Route("/places/{id}", name="PLACE_EDIT", methods={"PUT"})
     * @param string $id
     * @return Response
     */
    public function placeEdit(string $id)
    {
        return $this->placeManager->init()->edit($id)->getResponse();
    }

    /**
     * @Route("/places/{id}", name="PLACE_DELETE", methods={"DELETE"})
     * @param string $id
     * @return Response
     */
    public function placeDelete(string $id)
    {
        return $this->placeManager->delete($id)->getResponse();
    }
}
