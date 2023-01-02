<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class SetAvatarImage implements SetAvatarImageInterface
{
    public function __construct(
        private readonly AvatarFileNameGeneratorInterface                      $fileNameGenerator,
        private readonly Filesystem                                            $filesystem,
        #[Autowire('%micro_post.profile_images_dir%')] private readonly string $publicDirectoryProfileImages,
    )
    {
    }

    public function set(
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

        $this->filesystem->copy($file, $this->publicDirectoryProfileImages . DIRECTORY_SEPARATOR . $fileName);

        if ($removeOldFile && $existAvatarFile) {
            $this->filesystem->remove($this->publicDirectoryProfileImages . DIRECTORY_SEPARATOR . $existAvatarFile);
        }
    }
}
