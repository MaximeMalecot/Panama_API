<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Subscription;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\SubscriptionPlanFixtures;
use App\Entity\SubscriptionPlan;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SubscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $freelancers = $manager->getRepository(User::class)->findByRole(['ROLE_FREELANCER_PREMIUM']);
        $subscriptionPlan = $manager->getRepository(SubscriptionPlan::class)->findOneBy([ 'stripeId' => 'price_1MRvKsDqNqmwdP0kJKPRXFVC']);
        if($freelancers){
            foreach($freelancers as $freelancer){
                $object = (new Subscription())
                        ->setPlan($subscriptionPlan)
                        ->setFreelancer($freelancer)
                        ->setIsActive(false) 
                    ;
                $manager->persist($object);
            }
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            SubscriptionPlanFixtures::class
        ];
    }
}

?>