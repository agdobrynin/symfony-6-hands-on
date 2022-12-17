<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Service\SetAvatarImageInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private SetAvatarImageInterface     $setAvatarImage,
        private ContainerBagInterface       $containerBag
    )
    {
    }

    public
    function load(ObjectManager $manager): void
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
        $fixtureUsers[] = new UserFixtureDto('user@email.com', [], 'User 😑', true);
        $fixtureUsers[] = new UserFixtureDto('view@email.com', [], 'Viewer 🖐', true);

        $avatarFixturesImagesDir = dirname(__FILE__) . '/profile_images/';
        $publicDirectoryProfileImages = $this->containerBag->get('micro_post.profile_images_dir');

        foreach ($fixtureUsers as $index => $userDto) {
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

            $avatarFixtureImage = $avatarFixturesImagesDir . $index . '.jpg';
            $avatarFile = new \SplFileInfo($avatarFixtureImage);

            if ($avatarFile->isFile()) {
                $this->setAvatarImage->set(
                    publicDirectoryProfileImages: $publicDirectoryProfileImages,
                    file: $avatarFile->getRealPath(),
                    fileExtension: $avatarFile->getExtension(),
                    user: $user,
                    moveFile: false
                );
            }

            $manager->persist($user);
            $users[] = $user;
        }

        $manager->flush();

        for ($i = 0; $i < 500; $i++) {
            $microPost = (new MicroPost())
                ->setContent($faker->realTextBetween(5, 140))
                ->setAuthor($users[array_rand($users)]);
            // set random likes
            foreach (array_rand($users, 3) as $index => $userIndex) {
                $microPost->addLikedBy($users[$userIndex]);
            }

            $manager->persist($microPost);
        }

        $manager->flush();

        // add comments for posts
        $posts = $manager->getRepository(MicroPost::class)->findAll();

        foreach ($posts as $post) {
            for ($c = 0; $c < rand(0, 50); $c++) {
                $comment = (new Comment())
                    ->setAuthor($users[array_rand($users)])
                    ->setContent($faker->realTextBetween(150, 300))
                    ->setMicroPost($post);

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
