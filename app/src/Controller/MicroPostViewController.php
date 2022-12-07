<?php

namespace App\Controller;

use App\Entity\MicroPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MicroPostViewController extends AbstractController
{
    #[Route('/micro/post/view/{id}', name: 'app_mp_view', methods: 'get')]
    public function index(MicroPost $post): Response
    {
        return $this->render('@mp/view.html.twig', ['post' => $post]);
    }
}
