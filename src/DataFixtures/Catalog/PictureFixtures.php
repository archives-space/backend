<?php

namespace App\DataFixtures\Catalog;

use App\DataFixtures\User\UserFixtures;
use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture\Exif;
use App\Document\Catalog\Picture\License;
use App\Document\Catalog\Picture;
use App\Document\Catalog\Picture\Position;
use App\Document\Catalog\Picture\Resolution;
use App\Document\File;
use App\Utils\Catalog\LicenseHelper;
use App\Utils\Catalog\PictureFileManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\Catalog\ResolutionHelper;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    const LOOP      = 100;
    const REFERENCE = 'picture_%s';

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var PictureFileManager
     */
    private $pictureFileManager;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * UserFixtures constructor.
     * @param DocumentManager    $dm
     * @param PictureFileManager $pictureFileManager
     */
    public function __construct(
        DocumentManager $dm,
        PictureFileManager $pictureFileManager
    )
    {
        $this->dm                 = $dm;
        $this->pictureFileManager = $pictureFileManager;
    }

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create('fr_FR');
        for ($i = 1; $i <= self::LOOP; $i++) {

            $picture = new Picture();

            $picture
//                ->setEdited($this->faker->boolean())
//                ->setOriginalFileName($filename)
//                ->setTypeMime('image/jpeg')
//                ->setHash(PictureHelpers::getHash($uploadedFile))
                ->setCreatedAt($this->faker->dateTimeBetween('-20 days', 'now'))
                ->setUpdatedAt($this->faker->optional()->dateTimeBetween('-20 days', 'now'))
            ;

            $this->setVersions($picture);

            if ($this->faker->boolean()) {
                $picture->setCatalog($this->getReference(sprintf(CatalogFixtures::REFERENCE, rand(1, CatalogFixtures::LOOP))));
            }

//            $this->pictureFileManager->upload($uploadedFile, $picture);

            $this->dm->persist($picture);
        }
        $this->dm->flush();
    }

    /**
     * @return UploadedFile
     */
    private function getImage(string $filename)
    {
        $imagesDir     = __DIR__ . '/image/*.*';
        $imagesDirCopy = __DIR__ . '/image/copy/';
        $files         = glob($imagesDir);

        $file = array_rand($files);
        $file = $files[$file];

        copy($file, $imagesDirCopy . $filename);

        return new UploadedFile($imagesDirCopy . $filename, $filename, null, null, true);
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
            $filename     = sprintf('%s.jpg', uniqid());
            $uploadedFile = $this->getImage($filename);

            $file = (new File())->setFile($uploadedFile);

            $resolution = (new Resolution())
                ->setFile($file)
                ->setWidth($this->faker->numberBetween(100, 8000))
                ->setHeight($this->faker->numberBetween(100, 8000))
                ->setFile($file)
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