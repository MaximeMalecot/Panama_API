<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\ClientInfo;
use App\DataFixtures\UserFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ClientInfoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $clients = $manager->getRepository(User::class)->findByRole(['ROLE_CLIENT']);
        if($clients){
            foreach($clients as $client){
                $object=(new ClientInfo)
                    ->setClient($client)
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