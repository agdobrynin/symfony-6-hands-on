<?php

namespace App\Security\Voter;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MicroPostVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [MicroPost::VOTER_EDIT, MicroPost::VOTER_VIEW])
            && $subject instanceof MicroPost;
    }

    /**
     * @param MicroPost $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (MicroPost::VOTER_VIEW === $attribute) {
            return true;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return true;
            }

            if (MicroPost::VOTER_EDIT === $attribute) {
                return $subject->getAuthor()->getId() === $user->getId()
                    || $this->security->isGranted('ROLE_EDITOR');
            }

            if (MicroPost::VOTER_VIEW === $attribute) {
                return true;
            }
        }

        return false;
    }
}
