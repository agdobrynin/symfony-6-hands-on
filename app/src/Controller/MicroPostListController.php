<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\MicroPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MicroPostListController extends AbstractController
{
    #[Route('/', name: 'app_micro_post_list', methods: 'get')]
    public function index(MicroPostRepository $repository): Response
    {
        $posts = $repository->getAllWithComments();

        return $this->render('@mp/list.html.twig', ['posts' => $posts]);
    }
}
