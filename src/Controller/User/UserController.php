<?php

namespace App\Controller\User;

use App\Provider\User\UserProvider;
use App\Repository\TotoRepository;
use App\Manager\User\UserManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller\User
 * @Route(defaults={"_format"="json"})
 */
class UserController extends AbstractController
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * DefaultController constructor.
     * @param UserManager        $userManager
     * @param UserProvider       $userProvider
     */
    public function __construct(
        UserManager $userManager,
        UserProvider $userProvider
    )
    {
        $this->userManager        = $userManager;
        $this->userProvider       = $userProvider;
    }

    /**
     * inscription
     * @Route("/register", name="REGISTER",methods={"POST"})
     * @return Response
     * @throws MongoDBException
     */
    public function register(): Response
    {
        return $this->userManager->init()->checkMissedField()->create()->getResponse();
    }

    /**
     * Création de la route "edit users"
     * @Route("/users/{id}", name="USERS_EDIT", methods={"PUT"})
     * @param string $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function userEdit(string $id)
    {
        return $this->userManager->init()->edit($id)->getResponse();
    }

    /**
     * Création de la route "profile"
     * @Route("/users/{id}", name="USER_DETAIL", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     */
    public function detail(string $id)
    {
        return $this->userProvider->getUserById($id)->getResponse();
    }

    /**
     * Création de la route "listing users"
     * @Route("/users", name="USERS", methods={"GET"})
     */
    public function users()
    {
        return $this->userProvider->getUsers()->getResponse();
    }

    /**
     * Création de la route "delete user"
     * @Route("/users/{id}", name="USER_DELETE", methods={"DELETE"})
     * @param string $id
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function delete(string $id)
    {
        return $this->userManager->delete($id)->getResponse();
    }

    /**
     * Création de la route "edit password"
     * @Route("/users/{id}/password", name="EDIT_PASSWORD", methods={"POST"})
     * @param string $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function editPassword(string $id)
    {
        return $this->userManager->init()->editPassword($id)->getResponse();
    }
}
