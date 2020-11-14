<?php

namespace App\Controller\User;

use App\Document\User;
use App\Repository\TotoRepository;
use App\Repository\UserRepository;
use App\Service\User\UserManager;
use App\Utils\User\UserArrayGenerator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Namshi\JOSE\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $user = $this->userManager->init()->checkMissedField()->create();

        return $this->json(
            array_merge(['success' => true,],
                $this->userArrayGenerator->userToArray($user)
            )
        );
    }

    /**
     * Création de la route "edit users"
     * @Route("/users/{id}", name="USERS", methods={"PUT"})
     * @param string $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function userEdit(string $id)
    {
        $user = $this->userManager->init()->edit($id);

        return $this->json(
            array_merge(['success' => true,],
                $this->userArrayGenerator->userToArray($user)
            )
        );
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
            return $this->json([
                'success' => false,
                'message' => 'No user found',
            ]);
        }

        return $this->json(
            array_merge(['success' => true,],
                $this->userArrayGenerator->userToArray($user)
            )
        );
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

        return $this->json(
            array_merge(['success' => true,],
                $users
            )
        );
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
            return $this->json([
                'success' => false,
                'message' => 'No user found',
            ]);
        }

        $this->dm->remove($user);
        $this->dm->flush();

        return $this->json([
            'success' => true,
        ]);
    }

    /**
     * Création de la route "edit password"
     * @Route("/users/{id}/password", name="EDIT_PASSWORD", methods={"POST"})
     * @param Request                      $request
     * @param string                       $id
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function editPassword(string $id)
    {
        $user = $this->userManager->init()->editPassword($id);

        return $this->json(
            array_merge(['success' => true,],
                $this->userArrayGenerator->userToArray($user)
            )
        );
    }


}
