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
        $faker = Factory::create('ru_RU');

        for ($i = 0; $i < 10; $i++) {
            $microPost = new MicroPost();
            $microPost->setContent($faker->realTextBetween(5, 140));
            $manager->persist($microPost);
        }

        $manager->flush();
    }
}
