<?php

namespace App\Repository;

use App\Dto\PaginatorItems;
use App\Entity\MicroPost;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function getPostsForIndex(int $page, int $pageSize): PaginatorItems
    {
        $query = $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            //
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);

        $ormPaginator = new Paginator($query);

        return new PaginatorItems($page, $pageSize, $ormPaginator->count(), $ormPaginator->getIterator());
    }

    public function getPostWithOtherData(Ulid|MicroPost $post): ?MicroPost
    {
        return $this->getAllQuery(
            withComments: true,
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

    public function getPostsByUser(int $page, $pageSize, User $user): PaginatorItems
    {
        $query = $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withProfile: true
        )
            ->where('postAuthor = :user')
            ->setParameter(':user', $user->getId()->toRfc4122())
            //
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);

        $paginator = new Paginator($query);

        return new PaginatorItems($page, $pageSize, $paginator->count(), $paginator->getIterator());
    }

    public function getFollowPosts(int $page, int $pageSize, User $user): PaginatorItems
    {
        $query = $this->getAllQuery(
            withComments: true,
            withLikes: true,
            withAuthor: true,
            withProfile: true
        )
            ->leftJoin('postAuthor.followers', 'authorFollowers')
            ->where('authorFollowers IN (:user)')
            ->setParameter(':user', $user->getId()->toRfc4122())
            //
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);;

        $paginator = new Paginator($query);

        return new PaginatorItems($page, $pageSize, $paginator->count(), $paginator->getIterator());
    }

    public function getPostsTopLiked(int $likeMoreOrEqual, $page, $pageSize): PaginatorItems
    {
        $countQuery = $this->createQueryBuilder('mp')
            ->innerJoin('mp.likedBy', 'usrLike')
            ->select('mp.id, count(usrLike) as HIDDEN likes')
            ->having('count(usrLike) >= :likeMoreOrEqual')
            ->setParameter(':likeMoreOrEqual', $likeMoreOrEqual)
            ->groupBy('mp.id');

        $totalItems = \count($countQuery->getQuery()->getSingleColumnResult());

        $resultQuery = $this->createQueryBuilder('mp')
            ->innerJoin('mp.likedBy', 'usrLike')
            ->select('count(usrLike) as HIDDEN likes', 'mp')
            ->innerJoin('mp.author', 'postAuthor')
            ->addSelect('postAuthor')
            ->leftJoin('postAuthor.userProfile', 'postAuthorUserProfile')
            ->addSelect('postAuthorUserProfile')
            ->leftJoin('mp.comments', 'postComment')
            ->addSelect('postComment')
            ->orderBy('likes', 'DESC')
            ->addOrderBy('mp.id', 'DESC')
            ->groupBy('mp.id, postAuthor.id, postAuthorUserProfile.id, postComment.id')
            ->having('count(usrLike) >= :likeMoreOrEqual')
            ->setParameter(':likeMoreOrEqual', $likeMoreOrEqual)
            ->groupBy('mp.id, postAuthor.id, postAuthorUserProfile.id, postComment.id')
            //
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize);

        return new PaginatorItems($page, $pageSize, $totalItems, (new Paginator($resultQuery))->getIterator());
    }

    public function fillLikeCount(\ArrayIterator $posts): void
    {
        $query = $this->createQueryBuilder('mp')
            ->innerJoin('mp.likedBy', 'usrLike')
            ->select('count(usrLike) as likes')
            ->addSelect('mp.id')
            ->where('mp IN (:posts)')
            ->setParameter(':posts', $this->getRfc4122Ids($posts))
            ->groupBy('mp.id');

        $result = $query->getQuery()->getResult();
        $postIds = array_column($result, 'id');
        $postTotalLike = array_column($result, 'likes');
        $mapPostIdTotalLike = array_combine($postIds, $postTotalLike);

        foreach ($posts as $post) {
            $post->setTotalLikes($mapPostIdTotalLike[(string)$post->getId()] ?? null);
        }
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
            $query->leftJoin('mp.likedBy', 'postLikedBy')
                ->addSelect('postLikedBy');
        }

        if ($withAuthor || $withProfile) {
            $query->innerJoin('mp.author', 'postAuthor')
                ->addSelect('postAuthor');
        }

        if ($withProfile) {
            $query->leftJoin('postAuthor.userProfile', 'userProfile')
                ->addSelect('userProfile');
        }

        return $query->orderBy('mp.id', 'desc');
    }

    private function getRfc4122Ids(Collection|\ArrayIterator|array $subjects): array
    {
        $ids = [];

        foreach ($subjects as $subject) {
            $ids[] = $subject->getId()->toRfc4122();
        }

        return $ids;
    }
}
