<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/post')]
class PostVoteController extends AbstractController
{
    #[Route('/{id}/like', name: 'post_like')]
    public function like(
        Post $post,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post->setLikes(
            $post->getLikes() + 1
        );

        $entityManager->flush();

        return $this->redirectToRoute(
            'app_post_show',
            [
                'id' => $post->getId()
            ]
        );
    }

    #[Route('/{id}/dislike', name: 'post_dislike')]
    public function dislike(
        Post $post,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $post->setDislikes(
            $post->getDislikes() + 1
        );

        $entityManager->flush();

        return $this->redirectToRoute(
            'app_post_show',
            [
                'id' => $post->getId()
            ]
        );
    }
}