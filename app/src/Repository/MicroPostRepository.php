<?php

namespace App\Repository;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;

/**
 * @extends ServiceEntityRepository<MicroPost>
 *
 * @method MicroPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicroPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicroPost[]    findAll()
 * @method MicroPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MicroPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicroPost::class);
    }

    public function add(MicroPost $post, bool $flush = false): void
    {
        $this->getEntityManager()->persist($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MicroPost $post, bool $flush = false): void
    {
        $this->getEntityManager()->remove($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPostsForIndex(): array
    {
        return $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->getQuery()
            ->getResult();
    }

    public function getPostsByAuthor(Ulid|User $author): array
    {
        return $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->where('mp.author = :author')
            ->setParameter(':author', $author instanceof User ? $author->getId()->toRfc4122() : $author)
            ->getQuery()
            ->getResult();
    }

    private function getAllQuery(
        bool $withComments = false,
        bool $withLikes = false,
        bool $withAuthor = false,
        bool $withProfile = false
    ): QueryBuilder
    {
        $query = $this->createQueryBuilder('mp');

        if ($withComments) {
            $query->leftJoin('mp.comments', 'comments')
                ->addSelect('comments');
        }

        if ($withLikes) {
            $query->leftJoin('mp.likedBy', 'likedBy')
                ->addSelect('likedBy');
        }

        if ($withAuthor || $withProfile) {
            $query->leftJoin('mp.author', 'author')
                ->addSelect('author');
        }

        if ($withProfile) {
            $query->leftJoin('author.userProfile', 'userProfile')
                ->addSelect('userProfile');
        }

        return $query->orderBy('mp.id', 'desc');
    }
}
