<?php

namespace App\Controller;

use App\Entity\MicroPost;
use App\Repository\MicroPostRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\String\u;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class LikeController extends AbstractController
{
    public function __construct(
        private RequestStack        $request,
        private MicroPostRepository $postRepository)
    {
    }

    #[Route('/like/{id}', name: 'app_like')]
    public function like(MicroPost $post): RedirectResponse|Response
    {
        try {
            $this->denyAccessUnlessGranted(MicroPost::VOTER_EXTRA_PRIVACY, $post, 'This post can like followers only');
        } catch (AccessDeniedException $deniedException) {
            return $this->render('@mp/extra_privacy_denied.html.twig', [
                'denyType' => 'Like post',
                'deniedMessage' => $deniedException->getMessage(),
                'user' => $post->getAuthor(),
            ])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $post->addLikedBy($this->getUser());
        $this->postRepository->add($post, true);
        $flashMessage = sprintf('Post "%s..." is like!', $this->getPartOfContent($post));
        $this->addFlash(FlashTypeServiceInterface::SUCCESS, $flashMessage);

        return $this->redirect($this->request->getCurrentRequest()->headers->get('referer'));
    }

    #[Route('/unlike/{id}', name: 'app_unlike')]
    public function unlike(MicroPost $post): RedirectResponse
    {
        $post->removeLikedBy($this->getUser());
        $this->postRepository->add($post, true);
        $flashMessage = sprintf('Post "%s" is unlike', $this->getPartOfContent($post));
        $this->addFlash(FlashTypeServiceInterface::SUCCESS, $flashMessage);

        return $this->redirect($this->request->getCurrentRequest()->headers->get('referer'));
    }

    private function getPartOfContent(MicroPost $post): string
    {
        return u($post->getContent())->slice(0, 25)->toString();
    }
}
