<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GameRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GameRepository $gameRepository, HttpClientInterface $client): Response
    {
        $games = $gameRepository->findBy([
            'isActive' => true
        ]);

        $quote = null;

        try {
            $response = $client->request(
                'GET',
                'https://zenquotes.io/api/random'
            );

            $data = $response->toArray();

            $quote = [
                'text' => $data[0]['q'],
                'author' => $data[0]['a']
            ];

        } catch (\Exception $e) {
            $quote = null;
        }

        return $this->render('home/index.html.twig', [
            'games' => $games,
            'quote' => $quote
        ]);
    }
}
