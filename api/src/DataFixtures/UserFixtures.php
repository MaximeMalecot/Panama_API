<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        
        $pwd = '$2y$13$ysK58wrGjIQyag8ZN31pzeVRMUmWga5wTNav7kgyts0gKCUGegSa.';
        $object=(new User)
            ->setName('user')
            ->setSurname('user')
            ->setEmail("user@user.com")
            ->setPassword($pwd)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
            ;
        $manager->persist($object);

        $object=(new User)
            ->setName('freelance')
            ->setSurname('freelance')
            ->setEmail("freelance@freelance.com")
            ->setPassword($pwd)
            ->setRoles(["ROLE_FREELANCER"])
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
            ;
        $manager->persist($object);
        
        $object=(new User)
            ->setName('freelance')
            ->setSurname('freelance')
            ->setEmail("freelanceprem@freelanceprem.com")
            ->setPassword($pwd)
            ->setRoles(["ROLE_FREELANCER_PREMIUM"])
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
            ;
        $manager->persist($object);


        $object=(new User)
            ->setName('client')
            ->setSurname('client')
            ->setEmail("client@client.com")
            ->setPassword($pwd)
            ->setRoles(["ROLE_CLIENT"])
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
            ;
        $manager->persist($object);;

        $object=(new User)
            ->setName('admin')
            ->setSurname('admin')
            ->setEmail("admin@admin.com")
            ->setPassword($pwd)
            ->setRoles(["ROLE_ADMIN"])
            ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
        ;
        $manager->persist($object);

        $manager->flush();
    }
}
