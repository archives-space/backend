<?php

namespace App\Controller\User;

use App\Document\User\User;
use App\Model\ApiResponse\ApiResponse;
use App\Repository\TotoRepository;
use App\Repository\UserRepository;
use App\Service\User\UserManager;
use App\Utils\User\UserArrayGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
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
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserArrayGenerator
     */
    private $userArrayGenerator;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * DefaultController constructor.
     * @param DocumentManager    $dm
     * @param UserRepository     $userRepository
     * @param UserArrayGenerator $userArrayGenerator
     * @param UserManager        $userManager
     */
    public function __construct(
        DocumentManager $dm,
        UserRepository $userRepository,
        UserArrayGenerator $userArrayGenerator,
        UserManager $userManager
    )
    {
        $this->dm                 = $dm;
        $this->userRepository     = $userRepository;
        $this->userArrayGenerator = $userArrayGenerator;
        $this->userManager        = $userManager;
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
        if (!$user = $this->userRepository->getUserById($id)) {
            return (new ApiResponse(null, 'No user found'))->getResponse();
        }

        return (new ApiResponse($this->userArrayGenerator->userToArray($user)))->getResponse();
    }

    /**
     * Création de la route "listing users"
     * @Route("/users", name="USERS", methods={"GET"})
     */
    public function users()
    {
        $users = array_map(function (User $user) {
            return $this->userArrayGenerator->userToArray($user);
        }, $this->userRepository->getAllUsers()->toArray());

        return (new ApiResponse($users))->getResponse();
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
        if (!$user = $this->userRepository->getUserById($id)) {
            return (new ApiResponse(null, 'No user found'))->getResponse();
        }

        $this->dm->remove($user);
        $this->dm->flush();

        return (new ApiResponse())->getResponse();
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
