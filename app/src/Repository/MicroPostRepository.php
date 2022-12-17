<?php

namespace App\Repository;

use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
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

    public function getPostWithOtherData(Ulid|MicroPost $post): ?MicroPost
    {
        return $this->getAllQuery(
            withCommentsAuthor: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->where('mp.id = :post')
            ->setParameter(':post', $post instanceof MicroPost ? $post->getId()->toRfc4122() : $post->toRfc4122())
            ->getQuery()
            ->getSingleResult();
    }

    public function getPostsByAuthors(array|Collection $authors): array
    {
        $ids = [];

        foreach ($authors as $author) {
            $ids[] = $author->getId()->toRfc4122();
        }

        if (empty($ids)) {
            return [];
        }


        return $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->where('mp.author IN (:authors)')
            ->setParameter(':authors', $ids)
            ->getQuery()
            ->getResult();
    }

    public function getPostsTopLiked(int $likeMoreOrEqual, ?int $limit = null): array
    {
        $query = $this->getAllQuery(
            withLikes: true,
        )->select('mp.id')
            ->groupBy('mp.id')
            ->having('COUNT(likedBy) >= :likeMoreOrEqual')
            ->setParameter(':likeMoreOrEqual', $likeMoreOrEqual);

        if ($limit) {
            $query->setMaxResults($limit);
        }

        $ids = $query->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        return $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->where('mp.id in (:ids)')
            ->setParameter(':ids', $ids)
            ->getQuery()->getResult();
    }

    public function getPostByUuidWithCommentsLikesAuthorsProfiles(string $uuid): ?MicroPost
    {
        return $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )->where('mp.id = :uuid')
            ->setParameter(':uuid', $uuid)
            ->getQuery()->getSingleResult();
    }

    private function getAllQuery(
        bool $withComments = false,
        bool $withCommentsAuthor = false,
        bool $withLikes = false,
        bool $withAuthor = false,
        bool $withProfile = false
    ): QueryBuilder
    {
        $query = $this->createQueryBuilder('mp');

        if ($withComments || $withCommentsAuthor) {
            $query->leftJoin('mp.comments', 'comments')
                ->addSelect('comments');
        }

        if ($withCommentsAuthor) {
            $query->leftJoin('comments.author', 'commentsAuthor')
                ->addSelect('commentsAuthor');
            $query->leftJoin('commentsAuthor.userProfile', 'commentsAuthorUserProfile')
                ->addSelect('commentsAuthorUserProfile');
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
