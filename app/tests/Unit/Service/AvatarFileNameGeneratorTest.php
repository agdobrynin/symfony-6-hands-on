<?php
declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\AvatarFileNameGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\UuidV4;

class AvatarFileNameGeneratorTest extends TestCase
{
    public function testGenerateFileName(): void
    {
        $user = (new User())->setEmail('test@email.com');
        $fileExtension = 'jpg';

        $slugger = new AsciiSlugger('en');

        $srv = new AvatarFileNameGenerator($slugger);
        $srv->generateFileName($user, $fileExtension);

        $fileName = $user->getUserProfile()->getAvatarImage();
        self::assertStringEndsWith('test-at-email-com.jpg', $fileName);
        $fileStartName = str_replace('-test-at-email-com.jpg', '', $fileName);
        self::assertTrue(UuidV4::isValid($fileStartName));
    }
}
