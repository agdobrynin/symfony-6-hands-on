<?php
declare(strict_types=1);

namespace App\DataFixtures;

class UserFixtureDto
{
    public function __construct(
        public readonly string $email,
        public readonly array  $roles,
        public readonly string $name,
        public readonly bool   $isVerified
    )
    {
    }
}
