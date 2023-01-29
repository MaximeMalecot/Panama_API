<?php

namespace App\DataFixtures;

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
        $freelancers = $manager->getRepository(User::class)->findByRole(['ROLE_FREELANCER', 'ROLE_FREELANCER_PREMIUM']);
        if($freelancers){
            foreach($freelancers as $freelancer){
                $object=(new FreelancerInfo)
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