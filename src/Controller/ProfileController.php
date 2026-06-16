<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProfileController extends AbstractController
{
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile instanceof UploadedFile) {

                $avatarName = uniqid().'.'.$avatarFile->guessExtension();

                $avatarFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/avatars',
                    $avatarName
                );

                $user->setAvatar($avatarName);
            }

            $bannerFile = $form->get('banner')->getData();

            if ($bannerFile instanceof UploadedFile) {

                $bannerName = uniqid().'.'.$bannerFile->guessExtension();

                $bannerFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/banners',
                    $bannerName
                );

                $user->setBanner($bannerName);
            }

            $entityManager->flush();

            return $this->redirectToRoute(
                'user_profile',
                ['username' => $user->getUsername()]
            );
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
