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

        $fixtureUsers[] = new UserFixtureDto(
            email: 'admin@email.com',
            roles: ['ROLE_ADMIN'],
            name: 'SurerAdmin 🛡',
            isVerified: true);

        $fixtureUsers[] = new UserFixtureDto('editor@email.com', ['ROLE_EDITOR'], 'Editor ✍', true);
        $fixtureUsers[] = new UserFixtureDto('monkey@email.com', [], 'Monkey man 🐵', true);
        $fixtureUsers[] = new UserFixtureDto('user@email.com', [], 'User 😑', false);

        foreach ($fixtureUsers as $userDto) {
            $profile = null;

            $profile = (new UserProfile())
                ->setName($userDto->name)
                ->setTwitterUsername($faker->userName())
                ->setLocation($faker->address());

            $user = (new User())
                ->setEmail($userDto->email)
                ->setUserProfile($profile)
                ->setRoles($userDto->roles);
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
