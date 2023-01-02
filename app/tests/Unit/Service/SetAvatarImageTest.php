<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Service\AvatarFileNameGenerator;
use App\Service\SetAvatarImage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\AsciiSlugger;

class SetAvatarImageTest extends TestCase
{
    public function getDataSource(): \Generator
    {
        yield 'Success test for new User' => [
            (new User())->setEmail('test@email.com'),
            'jpg',
            '-test-at-email-com.jpg'
        ];

        $profile = new UserProfile();
        // old file name
        $profile->setAvatarImage('abc-abc-test-email-com.jpg');

        yield 'Success test with remove old file avatar' => [
            (new User())->setEmail('test@email.com')->setUserProfile($profile),
            'jpg',
            '-test-at-email-com.jpg'
        ];
    }

    /**
     * @dataProvider getDataSource
     */
    public function testSetMethod(User $user, string $origFileExtension, string $fileNameEnd): void
    {
        $file = '/tmp/abc.' . $origFileExtension;
        $publicDirectoryProfileImages = '/home/www/images/profile/';

        $fileSystem = self::createMock(Filesystem::class);
        $fileSystem->expects(self::once())
            ->method('copy')->with($file);

        $avatarFileNameGenerator = new AvatarFileNameGenerator(new AsciiSlugger('en'));

        if ($existFile = $user->getUserProfile()?->getAvatarImage()) {
            $fileSystem->expects(self::once())
                ->method('remove')
                ->with($publicDirectoryProfileImages . DIRECTORY_SEPARATOR . $existFile);
        }

        $srv = new SetAvatarImage($avatarFileNameGenerator, $fileSystem, $publicDirectoryProfileImages);

        $srv->set($file, $origFileExtension, $user);

        self::assertStringEndsWith($fileNameEnd, $user->getUserProfile()->getAvatarImage());
    }
}
