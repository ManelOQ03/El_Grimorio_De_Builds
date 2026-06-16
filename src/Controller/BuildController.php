<?php

namespace App\Controller;

use App\Entity\Build;
use App\Entity\Game;
use App\Repository\GameRepository;
use App\Form\BuildType;
use App\Repository\BuildRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/build')]
final class BuildController extends AbstractController
{
    #[Route(name: 'app_build_index', methods: ['GET'])]
    public function index(BuildRepository $buildRepository): Response
    {
        return $this->render('build/index.html.twig', [
            'builds' => $buildRepository->findAll(),
        ]);
    }

    /*#[Route('/new', name: 'app_build_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $build = new Build();

        $form = $this->createForm(BuildType::class, $build);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $build->setCreatedAt(
                new \DateTimeImmutable()
            );

            $build->setUser(
                $this->getUser()
            );

            $entityManager->persist($build);
            $entityManager->flush();

            return $this->redirectToRoute('app_build_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('build/new.html.twig', [
            'build' => $build,
            'form' => $form,
        ]);
    }*/

    #[Route('/game/{slug}/build/new', name: 'app_game_build_new')]
    public function newForGame(string $slug, Request $request, EntityManagerInterface $entityManager, GameRepository $gameRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $game = $gameRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$game) {
            throw $this->createNotFoundException();
        }

        $build = new Build();

        $form = $this->createForm(BuildType::class, $build);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $build->setCreatedAt(
                new \DateTimeImmutable()
            );

            $build->setUser(
                $this->getUser()
            );

            $build->setGame(
                $game
            );

            $entityManager->persist($build);
            $entityManager->flush();

            return $this->redirectToRoute(
                'game_show',
                [
                    'slug' => $game->getSlug()
                ]
            );
        }

        return $this->render('build/new.html.twig', [
            'build' => $build,
            'form' => $form,
            'game' => $game
        ]);
    }

    #[Route('/{id}', name: 'app_build_show', methods: ['GET'])]
    public function show(Build $build): Response
    {
        return $this->render('build/show.html.twig', [
            'build' => $build,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_build_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Build $build, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($build->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(BuildType::class, $build);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_build_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('build/edit.html.twig', [
            'build' => $build,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_build_delete', methods: ['POST'])]
    public function delete(Request $request, Build $build, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($build->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$build->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($build);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_build_index', [], Response::HTTP_SEE_OTHER);
    }
}
