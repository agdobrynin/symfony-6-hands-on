<?php
declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserCheckerTest extends \PHPUnit\Framework\TestCase
{
    public function getSourceData(): \Generator
    {
        yield 'User is banned' => [
            (new User())->setBannedUntil(new \DateTime('+1 hour'))
        ];

        yield 'User not banned' => [
            new User()
        ];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testCheckPreAuth(User $user): void
    {
        if ($user->getBannedUntil()) {
            self::expectException(AccessDeniedHttpException::class);
        }

        (new UserChecker())->checkPreAuth($user);
        self::expectNotToPerformAssertions();
    }
}
