<?php

namespace App\Repository\User;

use App\Document\User\User;
use App\Provider\BaseProvider;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceDocumentRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int|null $nbPerPage
     * @param int|null $page
     * @return array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllUsersPaginate(int $nbPerPage = null, int $page = null)
    {
        $qb = $this->createQueryBuilder('u')
                   ->sort('username', 'ASC')
        ;

        $nbTotalResult = count($qb->getQuery()->execute()->toArray());

        if ($nbPerPage) {
            $qb->limit($nbPerPage)
               ->skip($page ?: 1)
            ;
        }

        return [
            BaseProvider::NB_TOTAL_RESULT => $nbTotalResult,
            BaseProvider::RESULT          => $qb->getQuery()->execute(),
        ];
    }

    /**
     * @param int|null $nbPerPage
     * @param int|null $page
     * @return array|Iterator|int|DeleteResult|InsertOneResult|UpdateResult|object|null
     * @throws MongoDBException
     */
    public function getAllUsers(?int $nbPerPage = 10, ?int $page = 1)
    {
        return $this->createQueryBuilder('u')
                    ->sort('username', 'ASC')
                    ->limit($nbPerPage)
                    ->skip($page)
                    ->getQuery()
                    ->execute()
            ;
    }

    /**
     * @param string $id
     * @return User|null
     */
    public function getUserById(string $id): ?User
    {
        return $this->createQueryBuilder('u')
                    ->field('id')->equals($id)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->createQueryBuilder('u')
                    ->field('username')->equals($username)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @param string $email
     * @return User|array|object|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
                    ->field('email')->equals($email)
                    ->getQuery()
                    ->getSingleResult()
            ;
    }

    /**
     * @param string|null $username
     * @param string|null $email
     * @return User|null
     */
    public function getUserByUsernameOrEmail(?string $username = null, ?string $email = null): ?User
    {
        $qb = $this->createQueryBuilder('u');

        if ($username) {
            $qb->addOr($qb->expr()->field('username')->equals($username));
        }
        if ($email) {
            $qb->addOr($qb->expr()->field('email')->equals($email));
        }

        return $qb->getQuery()
                  ->getSingleResult()
            ;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->dm->persist($user);
        $this->dm->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
