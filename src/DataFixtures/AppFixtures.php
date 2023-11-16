<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(
            (new User())
                ->setIdentifier('user')
        );

        $manager->persist(
            (new User())
                ->setIdentifier('admin')
                ->setRoles([User::ROLES['admin']])
        );

        $manager->flush();
    }
}
