<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileViewController extends AbstractController
{
    #[Route('/profile/{id}/view', name: 'app_profile_view')]
    public function index(User $user): Response
    {
        return $this->render('@main/profile_view/index.html.twig', [
            'user' => $user,
        ]);
    }
}
