<?php

namespace App\Controller;

use App\Entity\MicroPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MicroPostViewController extends AbstractController
{
    #[Route('/micro-post/{id}/view', name: 'app_micro_post_view', methods: 'get')]
    public function index(MicroPost $post): Response
    {
        return $this->render('@mp/view.html.twig', ['post' => $post]);
    }
}
