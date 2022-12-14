<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostListController extends AbstractController
{
    #[Route('/', name: 'app_micro_post_list', methods: 'get')]
    public function index(MicroPostRepository $repository): Response
    {
        $posts = $repository->getPostsForIndex();

        return $this->render('@mp/list.html.twig', ['posts' => $posts]);
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
        $posts = $repository->getPostsByAuthors($currentUser->getFollowing());

        return $this->render('@mp/follows.html.twig', ['posts' => $posts]);
    }
}
