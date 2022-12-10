<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentAddController extends AbstractController
{
    #[Route('/micro-post/{id}/comment', name: 'app_comment_add', methods: 'post|get')]
    public function index(MicroPost $post, Request $request, CommentRepository $commentRepository): RedirectResponse|Response
    {
        $form = $this->createForm(CommentType::class, new Comment());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setMicroPost($post);
            $commentRepository->save($comment, true);
            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Comment was added');

            return $this->redirectToRoute('app_micro_post_view', ['id' => $post->getId()->toRfc4122()]);
        }

        return $this->render('@mp/comment.html.twig', ['form' => $form, 'post' => $post]);
    }
}
