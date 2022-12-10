<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('ru_RU');

        $users = [];
        $userPassword = '12345678';

        for ($i = 0; $i < 3; $i++) {
            $user = (new User())->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, $userPassword));
            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $microPost = (new MicroPost())
                ->setContent($faker->realTextBetween(5, 140))
                ->setAuthor($users[array_rand($users)]);
            $manager->persist($microPost);
        }

        $manager->flush();
    }
}
