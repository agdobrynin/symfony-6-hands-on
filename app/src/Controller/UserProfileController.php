<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileUploadImageType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use App\Service\FlashTypeServiceInterface;
use App\Service\SetAvatarImageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/user/profile')]
class UserProfileController extends AbstractController
{
    #[Route('/edit', name: 'app_user_profile_edit')]
    public function edit(Request $request, UserRepository $userRepository): Response
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

            return $this->redirectToRoute('app_user_profile_view');
        }

        return $this->render('@main/user_profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/upload-image', name: 'app_user_profile_upload_image')]
    public function uploadImage(
        Request                 $request,
        UserRepository          $userRepository,
        SetAvatarImageInterface $setAvatarImage
    ): Response
    {
        $form = $this->createForm(ProfileUploadImageType::class);
        /** @var User $user */
        $user = $this->getUser();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadFile */
            $uploadFile = $form->get('avatar')->getData();

            $setAvatarImage->set(
                file: $uploadFile->getFileInfo()->getRealPath(),
                fileExtension: $uploadFile->getClientOriginalExtension(),
                user: $user,
                moveFile: true
            );

            $userRepository->save($user, true);

            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Avatar image was updated');

            return $this->redirectToRoute('app_user_profile_view');
        }

        return $this->render('@main/user_profile/image_upload.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/view', name: 'app_user_profile_view')]
    public function view(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('@main/user_profile/view.html.twig', ['user' => $user]);
    }
}
