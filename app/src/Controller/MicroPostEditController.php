<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostEditController extends AbstractController
{
    #[Route('/micro-post/{id}/edit', name: 'app_micro_post_edit')]
    #[IsGranted(MicroPost::VOTER_EDIT, 'post', 'Access denny. Edit post can owner or admin.')]
    public function index(
        MicroPost           $post,
        Request             $request,
        MicroPostRepository $microPostRepository
    ): Response|RedirectResponse
    {
        $form = $this->createForm(MicroPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $microPostRepository->add($post, true);
            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Micro post have been updated');

            return $this->redirectToRoute('app_micro_post_view', ['id' => $post->getId()->toRfc4122()]);
        }

        return $this->render('@mp/edit.html.twig', ['form' => $form]);
    }
}
