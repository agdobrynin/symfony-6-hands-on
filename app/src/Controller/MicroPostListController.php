<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\PaginatorDto;
use App\Entity\User;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostListController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Route('/', name: 'app_micro_post_list', methods: 'get')]
    public function index(MicroPostRepository $repository): Response
    {
        $page = (int)$this->requestStack->getCurrentRequest()->get('page', 1);
        $pageSize = $this->getParameter('micro_post.page_size_on_index_page');
        $totalItems = $repository->getPostsCountForIndex();
        $paginatorDto = new PaginatorDto($page, $totalItems, $pageSize);
        $posts = $repository->getPostsForIndex($paginatorDto);

        return $this->render('@mp/list.html.twig', [
            'posts' => $posts,
            'paginator' => $paginatorDto
        ]);
    }

    #[Route('/top-likes', name: 'app_micro_post_list_top_likes', methods: 'get')]
    public function topLikes(MicroPostRepository $repository): Response
    {
        $minLikes = $this->getParameter('micro_post.top_likes.min');
        $maxResultOfLikes = $this->getParameter('micro_post.top_likes.max_result');
        $posts = $repository->getPostsTopLiked($minLikes, $maxResultOfLikes);

        return $this->render('@mp/top_likes.html.twig', ['posts' => $posts]);
    }

    #[Route('/follows', name: 'app_micro_post_list_follows', methods: 'get')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(MicroPostRepository $repository): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $followAuthors = $currentUser->getFollowing();
        $page = (int)$this->requestStack->getCurrentRequest()->get('page', 1);
        $pageSize = $this->getParameter('micro_post.page_size_on_index_page');

        $totalItems = $repository->getPostsByAuthorsCount($followAuthors);

        $paginatorDto = new PaginatorDto($page, $totalItems, $pageSize);

        $posts = $repository->getPostsByAuthors($followAuthors, $paginatorDto);

        return $this->render('@mp/follows.html.twig', [
            'posts' => $posts,
            'paginator' => $paginatorDto
        ]);
    }
}
