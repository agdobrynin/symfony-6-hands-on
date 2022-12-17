<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\UuidV4;

class SetAvatarImage implements SetAvatarImageInterface
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly Filesystem       $filesystem
    )
    {
    }

    public function set(
        string $publicDirectoryProfileImages,
        string $file,
        string $fileExtension,
        User   $user,
        bool   $removeOldFile = true,
        bool   $moveFile = true
    ): void
    {
        $fileName = sprintf('%s-%s.%s', (new UuidV4())->toRfc4122(), $this->slugger->slug($user->getEmail()), $fileExtension);
        $this->filesystem->copy($file, $publicDirectoryProfileImages . '/' . $fileName);
        $profile = $user->getUserProfile() ?? new UserProfile();
        $existAvatarFile = $profile->getAvatarImage();

        $profile->setAvatarImage($fileName);
        $user->setUserProfile($profile);

        if ($existAvatarFile) {
            $this->filesystem->remove($publicDirectoryProfileImages . '/' . $existAvatarFile);
        }
    }
}
