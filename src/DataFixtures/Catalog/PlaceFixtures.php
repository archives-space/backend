<?php

namespace App\DataFixtures\Catalog;

use App\Document\Catalog\Catalog;
use App\Document\Catalog\Picture\Place;
use App\Document\Catalog\Picture\Position;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PlaceFixtures extends Fixture
{
    const LOOP      = 10;
    const REFERENCE = 'place_%s';

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * UserFixtures constructor.
     * @param DocumentManager $dm
     */
    public function __construct(
        DocumentManager $dm
    )
    {
        $this->dm = $dm;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= self::LOOP; $i++) {
            $place = new Place();

            $place->setName($faker->realText(50))
                    ->setDescription($faker->optional()->realText(500))
                    ->setWikidata($faker->optional()->url())
                    ->setPosition(new Position($faker->latitude(), $faker->longitude()))
                    ->setCreatedAt($faker->dateTimeBetween('-20 days', 'now'))
                    ->setUpdatedAt($faker->optional()->dateTimeBetween('-20 days', 'now'))
            ;

            $this->addReference(sprintf(self::REFERENCE, $i), $place);

            $this->dm->persist($place);
        }
        $this->dm->flush();
    }
}