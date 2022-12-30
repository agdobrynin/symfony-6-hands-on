<?php

namespace App\Service;

use App\Entity\User;

interface AvatarFileNameGeneratorInterface
{
    /**
     * Set into user profile image file name.
     */
    public function generateFileName(User $user, string $fileExtension): void;
}
