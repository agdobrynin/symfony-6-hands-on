<?php
declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Security\Voter\MicroPostVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Uid\Ulid;

class MicroPostVoterTest extends \PHPUnit\Framework\TestCase
{
    public function getSourceData(): \Generator
    {
        $userAdmin = self::createMock(User::class);
        $userAdmin->method('getId')->willReturn(new Ulid());
        $userAdmin->method('getRoles')->willReturn(['ROLE_ADMIN']);

        yield 'Edit grant for auth user with role admin' => [
            $userAdmin,
            (new MicroPost())->setAuthor((new User())->setEmail('email@email.com')),
            MicroPost::VOTER_EDIT,
            VoterInterface::ACCESS_GRANTED,
        ];

        yield 'Extra privacy grant for auth user with role admin' => [
            $userAdmin,
            (new MicroPost())->setAuthor((new User())->setEmail('email@email.com'))->setExtraPrivacy(true),
            MicroPost::VOTER_EXTRA_PRIVACY,
            VoterInterface::ACCESS_GRANTED,
        ];

        $user = self::createMock(User::class);
        $user->method('getId')->willReturn(new Ulid());

        yield 'Edit not grant for not owner post' => [
            $user,
            (new MicroPost())->setAuthor($userAdmin),
            MicroPost::VOTER_EDIT,
            VoterInterface::ACCESS_DENIED,
        ];

        $userEditor = self::createMock(User::class);
        $userEditor->method('getId')->willReturn(new Ulid());
        $userEditor->method('getRoles')->willReturn(['ROLE_EDITOR']);

        yield 'Edit grant for post with role editor' => [
            $userEditor,
            (new MicroPost())->setAuthor($userAdmin),
            MicroPost::VOTER_EDIT,
            VoterInterface::ACCESS_GRANTED,
        ];

        $userOwner = self::createMock(User::class);
        $userOwner->method('getId')->willReturn(new Ulid());

        yield 'Edit grant for owner post' => [
            $userOwner,
            (new MicroPost())->setAuthor($userOwner),
            MicroPost::VOTER_EDIT,
            VoterInterface::ACCESS_GRANTED,
        ];


        $user = self::createMock(User::class);
        $user->method('getId')->willReturn(new Ulid());

        $collection = self::createMock(ArrayCollection::class);
        $collection->method('contains')->with($user)->willReturn(false);

        $userAdmin->method('getFollowers')->willReturn($collection);

        yield 'Extra privacy not grant not follower' => [
            $user,
            (new MicroPost())->setAuthor($userAdmin)->setExtraPrivacy(true),
            MicroPost::VOTER_EXTRA_PRIVACY,
            VoterInterface::ACCESS_DENIED,
        ];


        $postOwner = self::createMock(User::class);
        $postOwner->method('getId')->willReturn(new Ulid());

        $userFollower = self::createMock(User::class);
        $userFollower->method('getId')->willReturn(new Ulid());
        $collection = self::createMock(ArrayCollection::class);

        $collection->method('contains')->with($userFollower)->willReturn(true);
        $postOwner->method('getFollowers')->willReturn($collection);

        yield 'Extra privacy grant for follower post author' => [
            $userFollower,
            (new MicroPost())->setAuthor($postOwner)->setExtraPrivacy(true),
            MicroPost::VOTER_EXTRA_PRIVACY,
            VoterInterface::ACCESS_GRANTED,
        ];

        yield 'Extra privacy grant for post owner user' => [
            $postOwner,
            (new MicroPost())->setAuthor($postOwner)->setExtraPrivacy(true),
            MicroPost::VOTER_EXTRA_PRIVACY,
            VoterInterface::ACCESS_GRANTED,
        ];
    }

    /**
     * @dataProvider getSourceData
     */
    public function testVoter(User $authUser, MicroPost $subject, string $attribute, int $voterAccess): void
    {
        // Auth user
        $token = self::createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($authUser);

        $isAdmin = \in_array('ROLE_ADMIN', $authUser->getRoles());
        $isEditor = \in_array('ROLE_EDITOR', $authUser->getRoles());

        $accessDecision = self::createMock(AccessDecisionManagerInterface::class);
        $accessDecision->expects(self::atLeastOnce())
            ->method('decide')->with($token)
            ->willReturn($isAdmin || $isEditor);

        $voter = new MicroPostVoter($accessDecision);

        self::assertSame($voterAccess, $voter->vote($token, $subject, [$attribute]));
    }
}
