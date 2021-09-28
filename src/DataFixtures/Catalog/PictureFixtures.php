<?php

namespace App\DataFixtures\Catalog;

use App\Document\Catalog\Picture\Version\Exif;
use App\Document\Catalog\Picture\Version\License;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Picture\Place\Position;
use App\Document\Catalog\Picture\Version\Resolution;
use App\Service\Catalog\PictureFileManager;
use App\Utils\Catalog\LicenseHelper;
use App\Utils\Catalog\ResolutionHelper;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    const LOOP      = 100;
    const REFERENCE = 'picture_%s';

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var PictureFileManager
     */
    private PictureFileManager $pictureFileManager;

    /**
     * UserFixtures constructor.
     * @param DocumentManager $dm
     * @param KernelInterface $kernel
     */
    public function __construct(
        DocumentManager $dm,
        KernelInterface $kernel,
        PictureFileManager $pictureFileManager
    )
    {
        $this->dm                 = $dm;
        $this->kernel             = $kernel;
        $this->pictureFileManager = $pictureFileManager;
    }

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create('fr_FR');
        for ($i = 1; $i <= self::LOOP; $i++) {

            $filename    = sprintf('%s.jpg', uniqid());
            $pictureFile = $this->getImage($filename);

            $picture = (new Picture())
                ->setCreatedAt($this->faker->dateTimeBetween('-20 days', 'now'))
                ->setUpdatedAt($this->faker->optional()->dateTimeBetween('-20 days', 'now'))
                ->setFile($pictureFile)
            ;

            $this->setVersions($picture);

            if ($this->faker->boolean()) {
                $picture->setCatalog($this->getReference(sprintf(CatalogFixtures::REFERENCE, rand(1, CatalogFixtures::LOOP))));
            }

            $this->pictureFileManager->upload($picture);

            $this->dm->persist($picture);
        }
        $this->dm->flush();
    }

    /**
     * @param string $filename
     * @return Picture\PictureFile
     */
    private function getImage(string $filename)
    {
        $imagesDir     = $this->kernel->getProjectDir() . '/src/DataFixtures/Catalog/image/*.*';
        $imagesDirCopy = $this->kernel->getProjectDir() . '/var/fixtures/';
        $files         = glob($imagesDir);

        $file = array_rand($files);
        $file = $files[$file];

        copy($file, $imagesDirCopy . $filename);

        $uploadedfile = new UploadedFile($imagesDirCopy . $filename, $filename, null, null, true);

        return (new Picture\PictureFile())
            ->setPath(uniqid('fixture', true))
            ->setSize($this->faker->numberBetween(1000, 5000000))
            ->setHash(uniqid('fixture-hash', true))
            ->setMimeType($this->faker->mimeType())
            ->setOriginalFileName('fixture.jpg')
            ->setUploadedFile($uploadedfile)
            ;
    }

    public function setVersions(Picture $picture)
    {
        foreach (range(1, random_int(2, 20)) as $i) {
            $version = (new Picture\Version())
                ->setName($this->faker->realText(100))
                ->setDescription($this->faker->optional()->realText(200))
                ->setSource($this->faker->optional()->url())
                ->setTakenAt($this->faker->dateTimeBetween('-20 days', 'now'))
                ->setCreatedAt($this->faker->dateTimeBetween('-20 days', 'now'))
//                ->setCreatedBy($this->getReference(sprintf(UserFixtures::REFERENCE, rand(1, UserFixtures::LOOP))))
            ;

            $this->setExif($version);
            $this->setPosition($version);
            $this->setPlace($version);
            $this->setResolutions($version);
            $this->setLicense($version);

            $picture->addVersion($version);
        }

        $picture->setValidatedVersion($this->faker->randomElement($picture->getVersions()->toArray()));
    }

    private function setExif(Picture\Version $version)
    {
        $exif = new Exif();
        $exif
            ->setModel($this->faker->optional()->realText(20))
            ->setManufacturer($this->faker->optional()->company())
            ->setAperture($this->faker->optional()->realText(20))
            ->setIso($this->faker->optional()->numberBetween(100, 8000))
            ->setExposure($this->faker->optional()->realText(20))
            ->setFocalLength($this->faker->optional()->randomFloat(0, 5))
            ->setFlash($this->faker->optional()->boolean())
        ;

        $version->setExif($exif);
    }

    private function setPosition(Picture\Version $version)
    {
        $position = new Position($this->faker->latitude(), $this->faker->longitude());
        $version->setPosition($position);
    }

    public function setPlace(Picture\Version $version)
    {
        if ($this->faker->boolean()) {
            return;
        }
        $place = $this->getReference(sprintf(PlaceFixtures::REFERENCE, rand(1, PlaceFixtures::LOOP)));

        $version->setPlace($place);
    }

    private function setResolutions(Picture\Version $version)
    {
        foreach (ResolutionHelper::RESOLUTIONS as $resolutionSlug) {

            $resolution = (new Resolution())
                ->setWidth($this->faker->numberBetween(100, 8000))
                ->setHeight($this->faker->numberBetween(100, 8000))
//                ->setPictureFile($pictureFile)
                ->setSlug($resolutionSlug)
            ;

            $version->addResolution($resolution);
        }
    }

    private function setLicense(Picture\Version $version)
    {
        $license = new License();
        $license->setName($this->faker->optional()->randomElement(LicenseHelper::getLicenses()));
        $license->setIsEdited($this->faker->optional()->boolean());

        $version->setLicense($license);
    }

    public function getDependencies()
    {
        return [
            CatalogFixtures::class,
            PlaceFixtures::class,
        ];
    }
}