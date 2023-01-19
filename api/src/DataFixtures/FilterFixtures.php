<?php

namespace App\DataFixtures;

use App\Entity\Filter;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class FilterFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $types = [ 'techno', 'autre'];

        for ($i = 0; $i < 10; $i++) {
            $object=(new Filter)
                ->setName($faker->name)
                ->setType($faker->randomElement($types))
            ;
            $manager->persist($object);
        }

        $manager->flush();
    }
}
