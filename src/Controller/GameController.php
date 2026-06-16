<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GameRepository;

final class GameController extends AbstractController
{
    #[Route('/game/{slug}', name: 'game_show')]
    public function show(string $slug, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$game) {
            throw $this->createNotFoundException();
        }

        return $this->render('game/index.html.twig', [
            'game' => $game,
            'builds' => $game->getBuilds()
        ]);
    }
    
    #[Route('/game/{slug}/forum', name: 'game_forum')]
    public function forum( string $slug, GameRepository $gameRepository ): Response
    {
        $game = $gameRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$game) {
            throw $this->createNotFoundException();
        }

        return $this->render('game/forum.html.twig', [
            'game' => $game,
            'posts' => $game->getPosts()
        ]);
    }
    
}
