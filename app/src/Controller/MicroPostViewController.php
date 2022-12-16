<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class MicroPostViewController extends AbstractController
{
    #[Route('/micro-post/{id}/view', name: 'app_micro_post_view', methods: 'get')]
    public function index(string $id, MicroPostRepository $microPostRepository): Response
    {
        $postUlid = Ulid::fromRfc4122($id);
        $post = $microPostRepository->getPostWithOtherData($postUlid);

        if (null === $post) {
            throw new NotFoundHttpException('Post not found');
        }

        $this->denyAccessUnlessGranted(MicroPost::VOTER_EXTRA_PRIVACY, $post, 'This post for followers only');

        return $this->render('@mp/view.html.twig', ['post' => $post]);
    }
}
