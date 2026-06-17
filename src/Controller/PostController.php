<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Game;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\GameRepository;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }



    #[Route('/game/{slug}/post/new', name: 'app_game_post_new')]
    public function newForGame( string $slug, Request $request, EntityManagerInterface $entityManager, GameRepository $gameRepository ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $game = $gameRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$game) {
            throw $this->createNotFoundException();
        }

        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post->setCreatedAt(
                new \DateTimeImmutable()
            );

            $post->setLikes(0);
            $post->setDislikes(0);

            $post->setUser(
                $this->getUser()
            );

            $post->setGame(
                $game
            );

            $imageFile = $form->get('image')->getData();

            if ($imageFile instanceof UploadedFile) {

                $imageName = uniqid().'.'.$imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/posts',
                $imageName
            );

                $post->setImage($imageName);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute(
                'game_forum',
                [
                    'slug' => $game->getSlug()
                ]
            );
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'game' => $game
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET', 'POST'])]
    public function show( Post $post, Request $request, EntityManagerInterface $entityManager ): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->denyAccessUnlessGranted('ROLE_USER');

            $comment->setUser($this->getUser());
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_show', [
                'id' => $post->getId()
            ]);
        }

        $comments = $post->getComments()->toArray();

        usort($comments, function ($a, $b) {
            return $b->getLikes() <=> $a->getLikes();
        });

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        if ($post->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(
                new \DateTimeImmutable()
            );
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (
            $post->getUser() !== $this->getUser()
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
