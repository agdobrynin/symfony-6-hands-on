<?php

namespace App\Security\Voter;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MicroPostVoter extends Voter
{
    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [MicroPost::VOTER_EDIT, MicroPost::VOTER_EXTRA_PRIVACY])
            && $subject instanceof MicroPost;
    }

    /**
     * @param MicroPost $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (MicroPost::VOTER_EXTRA_PRIVACY === $attribute && !$subject->isExtraPrivacy()) {
            return true;
        }

        $user = $token->getUser();

        if ($user instanceof User) {
            if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
                return true;
            }

            if (MicroPost::VOTER_EDIT === $attribute) {
                return $subject->getAuthor()->getId()->equals($user->getId())
                    || $this->accessDecisionManager->decide($token, ['ROLE_EDITOR']);
            }

            if (MicroPost::VOTER_EXTRA_PRIVACY === $attribute) {
                $ownerOfSubject = $subject->getAuthor();

                if (($ownerOfSubject->getId()->equals($user->getId())
                    || $ownerOfSubject->getFollowers()->contains($user))) {
                    return true;
                }
            }
        }

        return false;
    }
}
