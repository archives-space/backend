<?php

namespace App\Service\User;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use voku\helper\EmailCheck;
use ZxcvbnPhp\Zxcvbn;

/**
 * Class UserManager
 * @package App\Service\User
 */
class UserManager
{
    const BODY_PARAM_USERNAME   = "username";
    const BODY_PARAM_PASSWORD   = "password";
    const BODY_PARAM_EMAIL      = "email";
    const BODY_PARAM_PUBLICNAME = "publicName";
    const BODY_PARAM_LOCATION   = "location";
    const BODY_PARAM_BIOGRAPHY  = "biography";
    const BODY_PARAM_ROLES      = "roles";

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @var Zxcvbn
     */
    private $zxcvbn;

    /**
     * UserManager constructor.
     * @param DocumentManager              $dm
     * @param UserRepository               $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param RequestStack                 $requestStack
     */
    public function __construct(
        DocumentManager $dm,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        RequestStack $requestStack
    )
    {
        $this->dm              = $dm;
        $this->userRepository  = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->requestStack    = $requestStack;
        $this->zxcvbn          = new Zxcvbn();
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->body = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function checkMissedField()
    {
        $missedFields = $this->missedFields();
        if (count($missedFields) > 0) {
            throw new \Exception(sprintf('Les champs suivants sont manquants : "%s"', implode(', ', $missedFields)));
        }
        return $this;
    }

    /**
     * @return User
     * @throws MongoDBException
     */
    public function create()
    {
        if ($user = $this->userRepository->getUserByUsernameOrEmail($this->body[self::BODY_PARAM_USERNAME], $this->body[self::BODY_PARAM_EMAIL])) {
            throw new \Exception('Ce username ou email existe déja');
        }

        $user = new User();
        $user->setUsername($this->body[self::BODY_PARAM_USERNAME]);
        $user->setPublicName($this->body[self::BODY_PARAM_PUBLICNAME] ?? null);
        $user->setLocation($this->body[self::BODY_PARAM_LOCATION] ?? null);
        $user->setBiography($this->body[self::BODY_PARAM_BIOGRAPHY] ?? null);

        $this->setRoles($user);
        $this->setEmail($user);
        $this->setPassword($user);

        $this->dm->persist($user);
        $this->dm->flush();

        return $user;

    }

    /**
     * @param string $id
     * @return User|null
     * @throws MongoDBException
     */
    public function edit(string $id)
    {
        if (!$user = $this->userRepository->getUserById($id)) {
            throw new \Exception('Cet utilisateur n\'existe pas');
        }

        $username = $this->body[self::BODY_PARAM_USERNAME] ?? $user->getUsername();
        // Si on change de username mais qu'il existe deja dans la db alors on throw une exception
        if ($user->getUsername() !== $username && $this->userRepository->getUserByUsername($username)) {
            throw new \Exception('Ce username existe déja');
        }
        $user->setUsername($username);

        $email = $this->body[self::BODY_PARAM_EMAIL] ?? $user->getEmail();
        // Si on change d'email mais qu'il existe deja dans la db alors on throw une exception
        if ($user->getEmail() !== $email && $this->userRepository->getUserByEmail($email)) {
            throw new \Exception('Cet email existe déja');
        }
        $user->setEmail($email);

        $user->setPublicName($this->body[self::BODY_PARAM_PUBLICNAME] ?? $user->getPublicName());
        $user->setLocation($this->body[self::BODY_PARAM_LOCATION] ?? $user->getLocation());
        $user->setBiography($this->body[self::BODY_PARAM_BIOGRAPHY] ?? $user->getBiography());

        $this->setRoles($user);
        $this->setPassword($user);

        $this->dm->flush();

        return $user;
    }

    /**
     * @param string $id
     * @return User|null
     * @throws \Exception
     */
    public function editPassword(string $id){
        if (!$user = $this->userRepository->getUserById($id)) {
            throw new \Exception('Cet utilisateur n\'existe pas');
        }

        $this->setPassword($user);

        return $user;
    }

    /**
     * @param User $user
     * @return null
     * @throws \Exception
     */
    private function setEmail(User $user)
    {
        if (!$email = $this->body[self::BODY_PARAM_EMAIL] ?? null) {
            return null;
        }

        if (!EmailCheck::isValid($email)) {
            throw new \Exception('Email non valide');
        }

        $user->setEmail($email);
    }

    /**
     * @param User $user
     */
    private function setRoles(User $user)
    {
        if (!$roles = $this->body[self::BODY_PARAM_ROLES] ?? null) {
            return;
        }

        $user->setRoles(is_array($roles) ? $roles : [$roles]);
    }

    /**
     * @param User $user
     * @return null
     * @throws \Exception
     */
    private function setPassword(User $user)
    {
        if (!$password = $this->body[self::BODY_PARAM_PASSWORD] ?? null) {
            return null;
        }

        if ($this->zxcvbn->passwordStrength($password)['score'] <= 1) {
            throw new \Exception('Mot de passe trop faible');
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
    }

    /**
     * @return string[]
     */
    private function missedFields()
    {
        if (!$this->body) {
            return $this->requiredField();
        }
        $missingKeys = array_diff_key(array_flip($this->requiredField()), $this->body);
        return array_intersect_key($this->requiredField(),
            array_flip($missingKeys)
        );
    }

    /**
     * @return string[]
     */
    private function requiredField()
    {
        return [
            self::BODY_PARAM_USERNAME,
            self::BODY_PARAM_PASSWORD,
            self::BODY_PARAM_EMAIL,
        ];
    }

    /**
     * @return string[]
     */
    private function field()
    {
        return [
            self::BODY_PARAM_USERNAME,
            self::BODY_PARAM_PASSWORD,
            self::BODY_PARAM_EMAIL,
            self::BODY_PARAM_PUBLICNAME,
            self::BODY_PARAM_LOCATION,
            self::BODY_PARAM_BIOGRAPHY,
            self::BODY_PARAM_ROLES,
        ];
    }
}