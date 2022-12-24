<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
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

        try {
            $this->denyAccessUnlessGranted(MicroPost::VOTER_EXTRA_PRIVACY, $post, 'This post for followers only');
        } catch (AccessDeniedException $deniedException) {
            return $this->render('@mp/extra_privacy_denied.html.twig', [
                'denyType' => 'View post',
                'deniedMessage' => $deniedException->getMessage(),
                'user' => $post->getAuthor(),
            ])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        return $this->render('@mp/view.html.twig', ['post' => $post]);
    }
}
