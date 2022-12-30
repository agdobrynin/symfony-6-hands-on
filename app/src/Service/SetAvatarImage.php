<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;

class SetAvatarImage implements SetAvatarImageInterface
{
    public function __construct(
        private readonly AvatarFileNameGeneratorInterface $fileNameGenerator,
        private readonly Filesystem                       $filesystem
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
        $existAvatarFile = $user->getUserProfile()?->getAvatarImage();

        $this->fileNameGenerator->generateFileName($user, $fileExtension);

        $fileName = $user->getUserProfile()->getAvatarImage();

        $this->filesystem->copy($file, $publicDirectoryProfileImages . DIRECTORY_SEPARATOR . $fileName);

        if ($removeOldFile && $existAvatarFile) {
            $this->filesystem->remove($publicDirectoryProfileImages . DIRECTORY_SEPARATOR . $existAvatarFile);
        }
    }
}
