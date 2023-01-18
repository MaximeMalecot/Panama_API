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

        // $object=(new User)
        //     ->setName('director')
        //     ->setSurname('director')
        //     ->setEmail("director@director.com")
        //     ->setPassword($pwd)
        //     ->setRoles(["ROLE_DIRECTOR"])
        //     ->setCreatedAt($faker->dateTimeBetween('-1 year', '-1 day'))
        //     ;
        // $manager->persist($object);

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
