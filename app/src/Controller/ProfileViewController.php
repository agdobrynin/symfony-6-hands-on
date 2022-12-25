<?php

namespace App\Controller;

use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProfileViewController extends AbstractController
{
    #[Route('/profile/{id}/view', name: 'app_profile_view')]
    public function index(
        string              $id,
        UserRepository      $userRepository,
        MicroPostRepository $microPostRepository,
        Request             $request
    ): Response
    {
        if ($user = $userRepository->getUserForUserProfilePage($id)) {
            $page = (int)$request->get('page', 1);
            $pageSize = $this->getParameter('blogger_profile.posts_list.page_size');
            $paginator = $microPostRepository->getPostsByUser($page, $pageSize, $user);
            $microPostRepository->fillLikeCount($paginator->iterator);
            $microPostRepository->fillCommentsCount($paginator->iterator);

            return $this->render('@main/profile_view/index.html.twig', [
                'user' => $user,
                'paginator' => $paginator,
            ]);
        }

        throw new NotFoundHttpException('User not found');
    }

    #[Route('/profile/{id}/followers', name: 'app_profile_followers')]
    public function followers(string $id, UserRepository $userRepository): Response
    {
        if ($user = $userRepository->getUserForUserProfileFollowers($id)) {
            return $this->render('@main/profile_view/followers.html.twig', [
                'user' => $user,
            ]);
        }

        throw new NotFoundHttpException('User not found');
    }

    #[Route('/profile/{id}/following', name: 'app_profile_following')]
    public function following(string $id, UserRepository $userRepository): Response
    {
        if ($user = $userRepository->getUserForUserProfileFollowing($id)) {
            return $this->render('@main/profile_view/following.html.twig', [
                'user' => $user,
            ]);
        }

        throw new NotFoundHttpException('User not found');
    }
}
