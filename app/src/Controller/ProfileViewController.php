<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileViewController extends AbstractController
{
    #[Route('/profile/{id}/view', name: 'app_profile_view')]
    public function index(User $user, MicroPostRepository $microPostRepository): Response
    {
        return $this->render('@main/profile_view/index.html.twig', [
            'user' => $user,
            'posts' => $microPostRepository->getPostsByAuthor($user)
        ]);
    }

    #[Route('/profile/{id}/followers', name: 'app_profile_followers')]
    public function followers(User $user): Response
    {
        return $this->render('@main/profile_view/followers.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/{id}/following', name: 'app_profile_following')]
    public function following(User $user): Response
    {
        return $this->render('@main/profile_view/following.html.twig', [
            'user' => $user,
        ]);
    }
}
