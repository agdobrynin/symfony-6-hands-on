<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function getUserForUserProfilePage(User|string $user): ?User
    {
        return $this->getUserQuery(
            withProfile: true,
            withFollowers: true,
            withFollowing: true
        )
            ->where('u = :user')
            ->setParameter(':user', $this->getIdAsRfc4122($user))
            ->getQuery()
            ->getSingleResult();
    }

    public function getUserForUserProfileFollowers(User|string $user): ?User
    {
        return $this->getUserQuery(
            withProfile: true,
            withPosts: true,
            withFollowers: true,
            withFollowing: true
        )
            ->where('u.id = :user_id')
            ->setParameter(':user_id', $this->getIdAsRfc4122($user))
            ->getQuery()
            ->getSingleResult();
    }

    public function getUserForUserProfileFollowing(User|string $user): ?User
    {
        return $this->getUserQuery(
            withProfile: true,
            withFollowing: true
        )
            ->where('u.id = :user_id')
            ->setParameter(':user_id', $this->getIdAsRfc4122($user))
            ->getQuery()
            ->getSingleResult();
    }

    private function getIdAsRfc4122(User|string $subject): string
    {
        if ($subject instanceof User) {
            return $subject->getId()->toRfc4122();
        }

        if (Ulid::isValid($subject)) {
            return Ulid::fromString($subject)->toRfc4122();
        }

        return Uuid::fromString($subject)->toRfc4122();
    }

    private function getUserQuery(
        bool $withProfile = false,
        bool $withPosts = false,
        bool $withFollowers = false,
        bool $withFollowing = false
    ): QueryBuilder
    {
        $query = $this->createQueryBuilder('u');

        if ($withProfile) {
            $query->leftJoin('u.userProfile', 'userProfile')
                ->addSelect('userProfile');
        }

        if ($withPosts) {
            $query->innerJoin('u.microPosts', 'microPosts')
                ->addSelect('microPosts')
                ->innerJoin('microPosts.author', 'microPostsAuthor')
                ->addSelect('microPostsAuthor');
        }

        if ($withFollowers) {
            $query->leftJoin('u.followers', 'followersUsers')
                ->addSelect('followersUsers')
                ->leftJoin('followersUsers.userProfile', 'followersUsersProfile')
                ->addSelect('followersUsersProfile');
        }

        if ($withFollowing) {
            $query->leftJoin('u.following', 'followingUsers')
                ->addSelect('followingUsers')
                ->leftJoin('followingUsers.userProfile', 'followingUsersProfile')
                ->addSelect('followingUsersProfile');
        }

        return $query;
    }
}
