<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostListController extends AbstractController
{
    private readonly int $page;

    public function __construct(
        private RequestStack                                                   $requestStack,
        #[Autowire('%env(int:PAGE_SIZE_ON_INDEX_PAGE)%')] private readonly int $pageSize,
        private readonly MicroPostRepository                                   $microPostRepository
    )
    {
        $this->page = (int)$this->requestStack->getCurrentRequest()->get('page', 1);
    }

    #[Route('/', name: 'app_micro_post_list', methods: 'get')]
    public function index(): Response
    {
        $paginatorItems = $this->microPostRepository->getPostsForIndex($this->page, $this->pageSize);
        $this->microPostRepository->fillLikeCount($paginatorItems->iterator);
        $this->microPostRepository->fillCommentsCount($paginatorItems->iterator);

        return $this->render('@mp/list.html.twig', [
            'paginator' => $paginatorItems
        ]);
    }

    #[Route('/top-likes', name: 'app_micro_post_list_top_likes', methods: 'get')]
    public function topLikes(): Response
    {
        $minLikes = $this->getParameter('micro_post.top_likes.min');
        $paginatorItems = $this->microPostRepository->getPostsTopLiked($minLikes, $this->page, $this->pageSize);
        $this->microPostRepository->fillLikeCount($paginatorItems->iterator);
        $this->microPostRepository->fillCommentsCount($paginatorItems->iterator);

        return $this->render('@mp/top_likes.html.twig', [
            'paginator' => $paginatorItems,
            'minLikes' => $minLikes
        ]);
    }

    #[Route('/follows', name: 'app_micro_post_list_follows', methods: 'get')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(): Response
    {
        $paginator = $this->microPostRepository->getFollowPosts($this->page, $this->pageSize, $this->getUser());
        $this->microPostRepository->fillLikeCount($paginator->iterator);
        $this->microPostRepository->fillCommentsCount($paginator->iterator);

        return $this->render('@mp/follows.html.twig', [
            'paginator' => $paginator
        ]);
    }
}
