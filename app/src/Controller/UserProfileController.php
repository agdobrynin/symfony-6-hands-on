<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserProfileController extends AbstractController
{
    #[Route('/user/profile/edit', name: 'app_user_profile_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userProfile = $user->getUserProfile() ?? new UserProfile();
        $form = $this->createForm(UserProfileType::class, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();
            $user->setUserProfile($userProfile);
            $userRepository->save($user, true);
            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Profile was updated');

            return $this->redirectToRoute('app_profile_view', ['id' => $user->getId()->toRfc4122()]);
        }

        return $this->render('@main/user_profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
