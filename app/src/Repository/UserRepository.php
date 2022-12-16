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

    public function getUserForUserProfilePage(User|Ulid $user): ?User
    {
        $ulid = $user instanceof User ? $user->getId()->toRfc4122() : $user->toRfc4122();

        return $this->getUserQuery(
            withProfile: true,
            withPosts: true,
            withFollowers: true,
            withFollowing: true
        )
            ->where('u = :user')
            ->setParameter(':user', $ulid)
            ->getQuery()
            ->getSingleResult();
    }

    private function getUserQuery(
        bool $withProfile = false,
        bool $withComments = false,
        bool $withLikes = false,
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

        if ($withComments) {
            $query->leftJoin('u.comments', 'comments')
                ->addSelect('comments');
        }

        if ($withLikes) {
            $query->leftJoin('u.likedPosts', 'likedPosts')
                ->addSelect('likedPosts');
        }

        if ($withPosts) {
            $query->leftJoin('u.microPosts', 'microPosts')
                ->addSelect('microPosts');
        }

        if ($withFollowers) {
            $query->leftJoin('u.followers', 'followers')
                ->addSelect('followers');
        }

        if ($withFollowing) {
            $query->leftJoin('u.following', 'following')
                ->addSelect('following');
        }

        return $query;
    }
}
