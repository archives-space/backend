<?php

namespace App\DataFixtures\User;

use App\Document\User\User;

use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    const LOOP = 100;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserFixtures constructor.
     * @param DocumentManager              $dm
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        DocumentManager $dm,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->dm              = $dm;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= self::LOOP; $i++) {
            $username  = $faker->userName . '-' . uniqid();
            $isDeleted = $faker->boolean();

            $user = new User();
            $user->setUsername($username)
                 ->setRoles($faker->randomElements([
                     User::ROLE_ADMIN,
                     User::ROLE_CONTRIBUTOR,
                     User::ROLE_MODERATOR,
                     User::ROLE_USER,
                 ]))
                 ->setPassword($this->passwordEncoder->encodePassword($user, $username))
                 ->setEmail($faker->freeEmail)
                 ->setIsLocked($faker->boolean())
                 ->setIsVerified($faker->boolean())
                 ->setIsDeleted($isDeleted)
                 ->setScore($faker->numberBetween(0, 5000000))
                 ->setLastLoginAt($faker->optional()->dateTimeBetween('-30 days', 'now'))
                 ->setCreateAt($faker->dateTimeBetween('-30 days', 'now'))
                 ->setUpdatedAt($faker->optional()->dateTimeBetween('-25 days', 'now'))
                 ->setDeletedAt($isDeleted ? $faker->dateTimeBetween('-20 days', 'now') : null)
                 ->setPublicName($faker->optional()->name)
                 ->setLocation($faker->optional()->address)
                 ->setBiography($faker->optional()->realText(200))
            ;
            $this->dm->persist($user);
        }
        $this->dm->flush();
    }
}
