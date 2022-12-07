<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $microPost = new MicroPost();
            $microPost->setTitle($faker->realTextBetween(50, 100));
            $microPost->setContent($faker->realTextBetween(250, 300));

            $manager->persist($microPost);
        }

        $manager->flush();
    }
}
