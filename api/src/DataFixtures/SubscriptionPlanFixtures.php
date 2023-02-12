<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\SubscriptionPlan;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Service\SubscriptionPlanService;

class SubscriptionPlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        //Dummy plan for the fake freelancer premium
        $faker = Factory::create();
        $object=(new SubscriptionPlan)
            ->setName("Dummy Plan | don't use it")
            ->setDescription("Not working, don\'t use it")
            ->setColor($faker->hexColor)
            ->setPrice(50)
            ->setStripeId('price_dummy')
        ;
        $manager->persist($object);
        $manager->flush();

        if( 
            isset($_ENV['STRIPE_SK']) 
            && !empty($_ENV['STRIPE_SK']) 
            && $_ENV['STRIPE_SK'] !== ""
        ){
            $subscriptionPlanService = new SubscriptionPlanService($manager);
            $subscriptionPlanService->createSubscriptionPlan("Premium Plan", 50);
        }

    }
}