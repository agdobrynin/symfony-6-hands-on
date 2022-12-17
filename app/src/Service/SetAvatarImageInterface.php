<?php

namespace App\Service;

use App\Entity\User;

interface SetAvatarImageInterface
{
    /**
     * Move file uploaded to public directory and set to user profile avatar image.
     */
    public function set(
        string $publicDirectoryProfileImages,
        string $file,
        string $fileExtension,
        User   $user,
        bool   $removeOldFile = true,
        bool   $moveFile = false
    ): void;
}
