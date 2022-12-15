<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileUploadImageType;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use App\Service\FlashTypeServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Uid\UuidV4;

class UserProfileController extends AbstractController
{
    #[Route('/user/profile/edit', name: 'app_user_profile_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

            return $this->redirectToRoute('app_profile_view', ['id' => $user->getId()->toRfc4122()]);
        }

        return $this->render('@main/user_profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/profile/upload-image', name: 'app_user_profile_upload_image')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function uploadImage(
        Request          $request,
        UserRepository   $userRepository,
        SluggerInterface $slugger,
        Filesystem       $filesystem
    ): Response
    {
        $form = $this->createForm(ProfileUploadImageType::class);
        /** @var User $user */
        $user = $this->getUser();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadFile */
            $uploadFile = $form->get('avatar')->getData();

            $fileName = sprintf('%s-%s.%s',
                (new UuidV4())->toRfc4122(),
                $slugger->slug($user->getEmail()),
                $uploadFile->getClientOriginalExtension()
            );

            $directoryProfileImages = $this->getParameter('micro_post.profile_images_dir');

            $uploadFile->move($directoryProfileImages, $fileName);

            $profile = $user->getUserProfile() ?? new UserProfile();
            $existAvatarFile = $profile->getAvatarImage();

            $profile->setAvatarImage($fileName);
            $user->setUserProfile($profile);
            $userRepository->save($user, true);

            if ($existAvatarFile) {
                $filesystem->remove($directoryProfileImages . '/' . $existAvatarFile);
            }

            $this->addFlash(FlashTypeServiceInterface::SUCCESS, 'Avatar image was updated');

            return $this->redirectToRoute('app_user_profile_upload_image');
        }

        return $this->render('@main/user_profile/image_upload.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
