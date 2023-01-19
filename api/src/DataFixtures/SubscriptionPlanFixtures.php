<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\SubscriptionPlan;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubscriptionPlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $object=(new SubscriptionPlan)
            ->setName($faker->name)
            ->setDescription($faker->text)
            ->setColor($faker->hexColor)
            ->setPrice(50)
            ->setStripeId('price_1MRvKsDqNqmwdP0kJKPRXFVC')
        ;
        $manager->persist($object);

        $manager->flush();
    }
}