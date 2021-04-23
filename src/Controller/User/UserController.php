<?php

namespace App\Controller\User;

use App\Provider\User\UserProvider;
use App\Manager\User\UserManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
    private UserManager $userManager;

    /**
     * @var UserProvider
     */
    private UserProvider $userProvider;

    /**
     * DefaultController constructor.
     * @param UserManager  $userManager
     * @param UserProvider $userProvider
     */
    public function __construct(
        UserManager $userManager,
        UserProvider $userProvider
    )
    {
        $this->userManager  = $userManager;
        $this->userProvider = $userProvider;
    }

    /**
     * inscription
     * @Route("/register", name="REGISTER",methods={"POST"})
     * @return Response
     * @throws MongoDBException
     * @throws ExceptionInterface
     */
    public function register(): Response
    {
        return $this->userManager->init()->create()->getResponse();
    }

    /**
     * @Route("/users/{id}", name="USERS_EDIT", methods={"PUT"})
     * @param string $id
     * @return JsonResponse
     * @throws Exception|ExceptionInterface
     */
    public function userEdit(string $id): JsonResponse
    {
        return $this->userManager->init()->edit($id)->getResponse();
    }

    /**
     * @Route("/users/{id}", name="USER_DETAIL", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function detail(string $id): JsonResponse
    {
        return $this->userProvider->init()->findByIdOrUsername($id)->getResponse();
    }

    /**
     * @Route("/users", name="USERS", methods={"GET"})
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws MongoDBException
     */
    public function users(): JsonResponse
    {
        return $this->userProvider->init()->findAll()->getResponse();
    }

    /**
     * @Route("/users/{id}", name="USER_DELETE", methods={"DELETE"})
     * @param string $id
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function delete(string $id): JsonResponse
    {
        return $this->userManager->delete($id)->getResponse();
    }

    /**
     * @Route("/users/{id}/password", name="EDIT_PASSWORD", methods={"POST"})
     * @param string $id
     * @return JsonResponse
     * @throws Exception|ExceptionInterface
     */
    public function editPassword(string $id): JsonResponse
    {
        return $this->userManager->init()->editPassword($id)->getResponse();
    }

    /**
     * @Route("/users/{id}/avatar", name="EDIT_AVATAR", methods={"POST"})
     * @param string $id
     * @return JsonResponse
     * @throws Exception|ExceptionInterface
     */
    public function editAvatar(string $id): JsonResponse
    {
        return $this->userManager->init()->editAvatar($id)->getResponse();
    }
}
