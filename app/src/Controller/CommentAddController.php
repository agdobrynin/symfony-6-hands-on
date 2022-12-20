<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\MicroPostRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentAddController extends AbstractController
{
    #[Route('/micro-post/{id}/comment', name: 'app_comment_add', methods: 'post|get')]
    #[IsGranted('ROLE_COMMENTER', null, 'You can\'t add comment with current roles')]
    public function index(string $id, Request $request, CommentRepository $commentRepository, MicroPostRepository $microPostRepository): RedirectResponse|Response
    {
        if ($post = $microPostRepository->getPostByUuidWithCommentsLikesAuthorsProfiles($id)) {
            try {
                $this->denyAccessUnlessGranted(MicroPost::VOTER_EXTRA_PRIVACY, $post, 'This post for followers only');
            } catch (AccessDeniedException $deniedException) {
                return $this->render('@mp/extra_privacy_denied.html.twig', [
                    'denyType' => 'Add comment',
                    'deniedMessage' => $deniedException->getMessage(),
                    'user' => $post->getAuthor(),
                ])
                    ->setStatusCode(Response::HTTP_FORBIDDEN);
            }

            $form = $this->createForm(CommentType::class, new Comment());
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Comment $comment */
                $comment = $form->getData();
                $comment->setMicroPost($post);
                $comment->setAuthor($this->getUser());
                $commentRepository->save($comment, true);
                $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Comment was added');

                return $this->redirectToRoute('app_micro_post_view', ['id' => $post->getId()->toRfc4122()]);
            }

            return $this->render('@mp/comment.html.twig', ['form' => $form, 'post' => $post]);
        }

        throw new NotFoundHttpException('Post not found');
    }
}
