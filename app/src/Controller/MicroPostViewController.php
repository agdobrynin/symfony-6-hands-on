<?php

namespace App\Controller;

use App\Entity\MicroPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostViewController extends AbstractController
{
    #[Route('/micro-post/{id}/view', name: 'app_micro_post_view', methods: 'get')]
    #[IsGranted(MicroPost::VOTER_EXTRA_PRIVACY, 'post', 'This post for followers only')]
    public function index(MicroPost $post): Response
    {
        return $this->render('@mp/view.html.twig', ['post' => $post]);
    }
}
