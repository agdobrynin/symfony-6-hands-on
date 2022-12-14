<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class FollowerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack           $requestStack
    )
    {
    }

    #[Route('/follow/{id}', name: 'app_follow')]
    public function follow(User $followToUser): Response
    {
        $user = $this->getUser();
        if ($user instanceof User && $user->getId() !== $followToUser->getId()) {
            $followToUser->follow($user);
            $this->em->flush();
        }

        return $this->redirect($this->requestStack->getCurrentRequest()->headers->get('referer'));
    }

    #[Route('/unfollow/{id}', name: 'app_unfollow')]
    public function unfollow(User $unfollowToUser): Response
    {
        $user = $this->getUser();

        if ($user instanceof User && $user->getId() !== $unfollowToUser->getId()) {
            $unfollowToUser->unfollow($user);
            $this->em->flush();
        }

        return $this->redirect($this->requestStack->getCurrentRequest()->headers->get('referer'));
    }
}
