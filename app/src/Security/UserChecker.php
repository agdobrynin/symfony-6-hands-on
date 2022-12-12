<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $bannedUntil = $user->getBannedUntil();

        if ($bannedUntil && $user->getBannedUntil() > new \DateTime('now')) {
            $message = sprintf('Your account is banned until %s', $bannedUntil->format(\DateTimeInterface::ATOM));

            throw new AccessDeniedHttpException($message);
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
