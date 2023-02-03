<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\FreelancerInfo;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FreelancerInfoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $freelancers = $manager->getRepository(User::class)->findByRole(['ROLE_FREELANCER', 'ROLE_FREELANCER_PREMIUM']);
        if($freelancers){
            foreach($freelancers as $freelancer){
                $object=(new FreelancerInfo)
                    ->setDescription($faker->text(200))
                    ->setPhoneNb("0101010101")
                    ->setAddress($faker->address)
                    ->setCity($faker->city)
                    ->setIsVerified(true)
                    ->setFreelancer($freelancer)
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
        ];
    }
}

?>