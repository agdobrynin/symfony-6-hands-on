<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\UuidV4;

class AvatarFileNameGenerator implements AvatarFileNameGeneratorInterface
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function generateFileName(User $user, string $fileExtension): void
    {
        $profile = $user->getUserProfile() ?? new UserProfile();
        $fileName = sprintf('%s-%s.%s', (new UuidV4())->toRfc4122(), $this->slugger->slug($user->getEmail()), $fileExtension);
        $profile->setAvatarImage($fileName);
        $user->setUserProfile($profile);
    }
}
