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

class MicroPostAddController extends AbstractController
{
    #[Route(
        '/micro-post/add',
        name: 'app_micro_post_add',
        methods: ['get', 'post'],
        priority: 2,
    )]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request, MicroPostRepository $microPostRepository): Response|RedirectResponse
    {
        $form = $this->createForm(MicroPostType::class, new MicroPost());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var MicroPost $microPost */
            $microPost = $form->getData();
            $microPost->setAuthor($this->getUser());
            $microPostRepository->add($microPost, true);
            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Micro post have been added');

            return $this->redirectToRoute('app_micro_post_view', ['id' => $microPost->getId()->toRfc4122()]);
        }

        return $this->render('@mp/add.html.twig', [
            'form' => $form,
        ]);
    }
}
