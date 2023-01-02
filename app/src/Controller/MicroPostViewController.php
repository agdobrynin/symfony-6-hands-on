<?php

namespace App\Controller;

use App\Dto\PaginatorItems;
use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class MicroPostViewController extends AbstractController
{
    private readonly int $page;

    public function __construct(
        RequestStack                                                      $requestStack,
        #[Autowire('%env(int:PAGE_SIZE_COMMENTS)%')] private readonly int $pageSize,
        private readonly MicroPostRepository                              $microPostRepository
    )
    {
        $this->page = (int)$requestStack->getCurrentRequest()->get('page', 1);
    }

    #[Route('/micro-post/{id}/view', name: 'app_micro_post_view', methods: 'get')]
    public function index(string $id): Response
    {
        $post = $this->microPostRepository->getPostViewWithComments(Ulid::fromRfc4122($id));

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

        $slicedComments = $post->getComments()->slice(($this->page - 1) * $this->pageSize, $this->pageSize);
        $iterator = new \ArrayIterator($slicedComments);
        $paginatorComments = new PaginatorItems($this->page, $this->pageSize, $post->getComments()->count(), $iterator);

        return $this->render('@mp/view.html.twig', [
            'post' => $post,
            'paginatorComments' => $paginatorComments
        ]);
    }
}
