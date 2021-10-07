<?php

namespace App\Controller\Catalog;

use App\Model\ApiResponse\ApiResponse;
use App\Provider\Catalog\PictureProvider;
use App\Manager\Catalog\PictureManager;
use App\Utils\Catalog\LicenseHelper;
use App\Utils\Catalog\PictureHelpers;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PictureController
 * @package App\Controller\Catalog
 * @Route(defaults={"_format"="json"})
 */
class PictureController extends AbstractController
{

    /**
     * @var PictureManager
     */
    private $pictureManager;

    /**
     * @var PictureProvider
     */
    private $pictureProvider;

    /**
     * PictureController constructor.
     * @param PictureManager  $pictureManager
     * @param PictureProvider $pictureProvider
     */
    public function __construct(
        PictureManager $pictureManager,
        PictureProvider $pictureProvider
    )
    {
        $this->pictureManager  = $pictureManager;
        $this->pictureProvider = $pictureProvider;
    }

    /**
     * @Route("/pictures", name="PICTURE_CREATE", methods="POST")
     * @return Response
     * @throws MongoDBException
     */
    public function pictureCreate(): Response
    {
        return $this->pictureManager->init()->create()->getResponse();
    }

    /**
     * @Route("/pictures/licenses", name="PICTURE_LICENSES", methods="GET")
     * @return Response
     */
    public function licenses(): Response
    {
        return (new ApiResponse(LicenseHelper::getLicenses()))->getResponse();
    }

    /**
     * Création de la route "PICTURES"
     * @Route("/pictures", name="PICTURES", methods={"GET"})
     * @return Response
     */
    public function pictures()
    {
        return $this->pictureProvider->init()->findAll()->getResponse();
    }

    /**
     * Création de la route "PICTURES"
     * @Route("/pictures/{id}", name="PICTURE_DETAIL", methods={"GET"})
     * @param string $id
     * @return Response
     */
    public function pictureDetail(string $id)
    {
        return $this->pictureProvider->findById($id)->getResponse();
    }

    /**
     * Création de la route "PICTURE_EDIT"
     * @Route("/pictures/{id}", name="PICTURE_EDIT", methods={"PUT"})
     * @param string $id
     * @return Response
     */
    public function pictureEdit(string $id)
    {
        return $this->pictureManager->init()->edit($id)->getResponse();
    }

    /**
     * Création de la route "PICTURE_DELETE"
     * @Route("/pictures/{id}", name="PICTURE_DELETE", methods={"DELETE"})
     * @param string $id
     * @return Response
     */
    public function pictureDelete(string $id)
    {
        return $this->pictureManager->delete($id)->getResponse();
    }

    /**
     * @Route("/pictures/{id}/object-changes", name="PICTURE_OBJECT_CHANGES_CREATE", methods={"POST"})
     * @param string $id
     * @return Response
     */
    public function pictureObjectChangesCreate(string $id)
    {
        return $this->pictureManager->init()->objectChangesCreate($id)->getResponse();
    }

    /**
     * @Route("/pictures/{id}/validate-changes", name="PICTURE_VALIDATE_CHANGES", methods={"POST"})
     * @param string $id
     * @return Response
     */
    public function pictureValidateChanges(string $id)
    {
        return $this->pictureManager->init()->validateChanges($id)->getResponse();
    }

    /**
     * @Route("/pictures/{id}/rejecte-changes", name="PICTURE_REJECTE_CHANGES", methods={"POST"})
     * @param string $id
     * @return Response
     */
    public function pictureRejecteChanges(string $id)
    {
        return $this->pictureManager->init()->rejecteChanges($id)->getResponse();
    }

    /**
     * @Route("/pictures/{id}/clear-changes", name="PICTURE_CLEAR_CHANGES", methods={"POST"})
     * @param string $id
     * @return Response
     */
    public function pictureClearChanges(string $id)
    {
        return $this->pictureManager->init()->clearChanges($id)->getResponse();
    }
}
