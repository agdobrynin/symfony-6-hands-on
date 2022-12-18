<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostListController extends AbstractController
{
    private readonly int $page;

    public function __construct(private RequestStack $requestStack, private readonly int $pageSize)
    {
        $this->page = (int)$this->requestStack->getCurrentRequest()->get('page', 1);
    }

    #[Route('/', name: 'app_micro_post_list', methods: 'get')]
    public function index(MicroPostRepository $repository): Response
    {
        $paginatorDto = $repository->getPostsForIndex($this->page, $this->pageSize);

        return $this->render('@mp/list.html.twig', [
            'posts' => $paginatorDto->getIterator(),
            'paginator' => $paginatorDto
        ]);
    }

    #[Route('/top-likes', name: 'app_micro_post_list_top_likes', methods: 'get')]
    public function topLikes(MicroPostRepository $repository): Response
    {
        $minLikes = $this->getParameter('micro_post.top_likes.min');
        $paginatorDto = $repository->getPostsTopLiked($minLikes, $this->page, $this->pageSize);

        return $this->render('@mp/top_likes.html.twig', [
            'paginator' => $paginatorDto,
            'minLikes' => $minLikes
        ]);
    }

    #[Route('/follows', name: 'app_micro_post_list_follows', methods: 'get')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(MicroPostRepository $repository): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $followAuthors = $currentUser->getFollowing();
        $paginatorDto = $repository->getPostsByAuthors($followAuthors, $this->page, $this->pageSize);

        return $this->render('@mp/follows.html.twig', [
            'paginator' => $paginatorDto
        ]);
    }
}
