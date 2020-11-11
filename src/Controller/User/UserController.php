<?php

namespace App\Controller\User;

use App\Document\User;
use App\Repository\TotoRepository;
use App\Repository\UserRepository;
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
     * DefaultController constructor.
     * @param DocumentManager    $dm
     * @param UserRepository     $userRepository
     * @param UserArrayGenerator $userArrayGenerator
     */
    public function __construct(
        DocumentManager $dm,
        UserRepository $userRepository,
        UserArrayGenerator $userArrayGenerator
    )
    {
        $this->dm                 = $dm;
        $this->userRepository     = $userRepository;
        $this->userArrayGenerator = $userArrayGenerator;
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
            array_merge([
                'success' => true,
            ],
                $this->userArrayGenerator->userToArray($user)
            )
        );
    }

    /**
     * @Route("/register", name="REGISTER",methods={"POST"})
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     * @throws MongoDBException
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $body = json_decode($request->getContent(), true);

        if (!$username = $body['username']) {
            throw new \Exception("username non fournis");
        }
        if (!$password = $body['password']) {
            throw new \Exception("password non fournis");
        }
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($passwordEncoder->encodePassword($user, $password));
        $this->dm->persist($user);
        $this->dm->flush();

        return $this->json([
            'success'  => true,
            'id'       => $user->getId(),
            'username' => $user->getUsername(),
            'role'     => $user->getRoles(),
        ]);
    }

    /**
     * Création de la route "users"
     * @Route("/users", name="USERS", methods={"GET"})
     */
    public function users()
    {
        $users = array_map(function (User $user) {
            return $this->userArrayGenerator->userToArray($user);
        }, $this->userRepository->getAllUsers()->toArray());

        return $this->json(
            array_merge([
                'success' => true,
            ],
                $users
            )
        );
    }

    /**
     * Création de la route "profile"
     * @Route("/users/{id}", name="USER_DELETE", methods={"DELETE"})
     * @param string $id
     * @return JsonResponse
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
     * Création de la route "promote"
     * @Route("/users/{id}/promote", name="PROMOTE", methods={"POST"})
     * @param Request $request
     * @param string  $id
     * @return Response
     * @throws MongoDBException
     */
    public function promote(Request $request, string $id)
    {
        $body = json_decode($request->getContent(), true);

        if (!$roles = $body['roles']) {
            throw new \Exception("roles non fournis");
        }

        if (!$user = $this->userRepository->getUserById($id)) {
            return $this->json([
                'success' => false,
                'message' => 'No user found',
            ]);
        }

        $user->setRoles($roles);
        $this->dm->flush();

        return $this->json(
            array_merge([
                'success' => true,
            ],
                $this->userArrayGenerator->userToArray($user)
            )
        );
    }

    /**
     * Création de la route "edit password"
     * @Route("/users/{id}/password", name="EDIT_PASSWORD", methods={"POST"})
     */
    public function editPassword(Request $request, string $id, UserPasswordEncoderInterface $passwordEncoder)
    {
        $body = json_decode($request->getContent(), true);

        if (!$password = $body['password']) {
            throw new \Exception("password non fournis");
        }

        if (!$user = $this->userRepository->getUserById($id)) {
            return $this->json([
                'success' => false,
                'message' => 'No user found',
            ]);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $password));
        $this->dm->flush();

        return $this->json(
            array_merge([
                'success' => true,
            ],
                $this->userArrayGenerator->userToArray($user)
            )
        );
    }


}
