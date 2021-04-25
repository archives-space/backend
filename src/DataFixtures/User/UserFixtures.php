<?php

namespace App\DataFixtures\User;

use App\Document\File;
use App\Document\User\User;

use App\Utils\FileManager;
use App\Utils\StringManipulation;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    const LOOP      = 100;
    const REFERENCE = 'user_%s';

    /**
     * @var DocumentManager
     */
    private DocumentManager $dm;

    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @var FileManager
     */
    private FileManager $fileManager;

    /**
     * UserFixtures constructor.
     * @param DocumentManager $dm
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FileManager $fileManager
     */
    public function __construct(
        DocumentManager $dm,
        UserPasswordEncoderInterface $passwordEncoder,
        FileManager $fileManager
    )
    {
        $this->dm = $dm;
        $this->passwordEncoder = $passwordEncoder;
        $this->fileManager = $fileManager;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= self::LOOP; $i++) {
            $username = $faker->userName() . '-' . uniqid();
            $isDeleted = $faker->boolean(5);

            $user = (new User());
            $user
                ->setUsername($username)
                ->setRoles($this->generateRole($faker))
                ->setPassword($this->passwordEncoder->encodePassword($user, $username))
                ->setEmail($faker->freeEmail())
                ->setIsLocked($faker->boolean())
                ->setIsVerified($faker->boolean())
                ->setIsDeleted($isDeleted)
                ->setScore($faker->numberBetween(0, 5000000))
                ->setLastLoginAt($faker->optional()->dateTimeBetween('-30 days', 'now'))
                ->setCreatedAt($faker->dateTimeBetween('-5 years', 'now'))
                ->setUpdatedAt($faker->optional()->dateTimeBetween('-100 days', 'now'))
                ->setDeletedAt($isDeleted ? $faker->dateTimeBetween('-20 days', 'now') : null)
                ->setPublicName($faker->optional()->name())
                ->setLocation($faker->optional()->address())
                ->setAvatar($faker->boolean() ? $this->generateAvatar($faker) : null)
                ->setBiography($faker->optional()->realText(200));

            $this->addReference(sprintf(self::REFERENCE, $i), $user);
            $this->dm->persist($user);
        }
        $this->dm->flush();
    }

    /**
     * @param Generator $faker
     * @return File
     */
    private function generateAvatar(Generator $faker): File
    {
        $type = $faker->randomElement([['png', 'image/png'], ['jpg', 'image/jpeg']]);
        $id = $faker->uuid();
        $originalName = StringManipulation::slugify($faker->text(20)) . $type[0];
        $path = '/tmp/' . $id . $type[0];
        copy('https://picsum.photos/200/', $path);

        $uploadedFile =  new UploadedFile($path, $originalName, $type[1], null, true);
        $file = $this->fileManager->parse($uploadedFile);
        $this->fileManager->upload($uploadedFile, $file);

        return $file;
    }

    /**
     * @param Generator $faker
     * @return string[]
     */
    private function generateRole(Generator $faker): array
    {
        $availableRoles = [
            User::ROLE_ADMIN,
            User::ROLE_MODERATOR,
            User::ROLE_CONFIRMED
        ];
        $baseRoleIndex = $faker->numberBetween(0, count($availableRoles)-1);
        $roles = [];
        for ($i = $baseRoleIndex; $i < count($availableRoles); $i++) {
            $roles[] = $availableRoles[$i];
        }
        return $roles;
    }
}
