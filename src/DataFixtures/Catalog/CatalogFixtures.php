<?php

namespace App\DataFixtures\Catalog;

use App\Document\Catalog\Catalog;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CatalogFixtures extends Fixture
{
    const LOOP      = 100;
    const REFERENCE = 'catalog_%s';

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
            $catalog = new Catalog();
            $catalog->setName($faker->realText(50))
                    ->setDescription($faker->optional()->realText(500))
                    ->setCreatedAt($faker->dateTimeBetween('-20 days', 'now'))
                    ->setUpdatedAt($faker->optional()->dateTimeBetween('-20 days', 'now'))
            ;

            $this->setParent($catalog, $i);

            $this->addReference(sprintf(self::REFERENCE, $i), $catalog);

            $this->dm->persist($catalog);
        }
        $this->dm->flush();
    }

    private function setParent(Catalog $catalog, int $i)
    {
        if (rand(0, 100) < 0) {
            return;
        }

        $reference = sprintf(self::REFERENCE, random_int(1, $i));

        if (!$this->hasReference($reference)) {
            return;
        }

        $catalog->setParent($this->getReference($reference));
    }
}