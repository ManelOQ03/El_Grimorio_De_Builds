<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/{id}/like', name: 'comment_like')]
    public function like( Comment $comment, EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $comment->setLikes(
            $comment->getLikes() + 1
        );

        $entityManager->flush();

        return $this->redirectToRoute(
            'app_post_show',
            [
                'id' => $comment->getPost()->getId()
            ]
        );
    }

    #[Route('/{id}/dislike', name: 'comment_dislike')]
    public function dislike( Comment $comment, EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $comment->setDislikes(
            $comment->getDislikes() + 1
        );

        $entityManager->flush();

        return $this->redirectToRoute(
            'app_post_show',
            [
                'id' => $comment->getPost()->getId()
            ]
        );
    }
    
    #[Route('/{id}/delete', name: 'comment_delete')]
    public function delete( Comment $comment, EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (
            $comment->getUser() !== $this->getUser()
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        $postId = $comment->getPost()->getId();

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute(
            'app_post_show',
            [
                'id' => $postId
            ]
        );
    }

    #[Route('/{id}/edit', name: 'comment_edit')]
    public function edit( Comment $comment, Request $request, EntityManagerInterface $entityManager ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        if ($comment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(
            CommentType::class,
            $comment
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setUpdatedAt(
                new \DateTimeImmutable()
            );

            $entityManager->flush();

            return $this->redirectToRoute(
                'app_post_show',
                [
                    'id' => $comment->getPost()->getId()
                ]
            );
        }

        return $this->render(
            'comment/edit.html.twig',
            [
                'form' => $form,
                'comment' => $comment
            ]
        );
    }
}