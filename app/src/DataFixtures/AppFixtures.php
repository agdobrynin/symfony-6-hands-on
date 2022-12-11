<?php

namespace App\DataFixtures;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserProfile;
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
        $faker = Factory::create();

        $users = [];
        $userPassword = '12345678';

        $fixtureUsers[] = new UserFixtureDto('admin@email.com', [User::ROLE_ADMIN]);
        $fixtureUsers[] = new UserFixtureDto('editor@email.com', [User::ROLE_EDITOR]);
        $fixtureUsers[] = new UserFixtureDto('commenter@email.com', [User::ROLE_COMMENTER]);
        $fixtureUsers[] = new UserFixtureDto('user@email.com', [User::ROLE_USER]);

        foreach ($fixtureUsers as $userDto) {
            $profile = (new UserProfile())
                ->setName($faker->name())
                ->setTwitterUsername($faker->userName())
                ->setLocation($faker->city());

            $user = (new User())->setEmail($userDto->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, $userPassword));
            $user->setUserProfile($profile)
                ->setRoles($userDto->roles);

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
