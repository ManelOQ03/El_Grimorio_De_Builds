<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\GameRepository;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

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
            'authorsCount' => count(array_unique(
                array_map(
                    fn($build) => $build->getUser()->getId(),
                    $game->getBuilds()->toArray()
                )
            )),
            'builds' => $game->getBuilds()
            
        ]);
    }
    
    #[Route('/game/{slug}/forum', name: 'game_forum')]
    public function forum( string $slug, GameRepository $gameRepository, PostRepository $postRepository, PaginatorInterface $paginator, Request $request ): Response
    {
        $game = $gameRepository->findOneBy(['slug' => $slug]);

        if (!$game) {
            throw $this->createNotFoundException();
        }

        $sort = $request->query->get('sort', 'new');

        if (!in_array($sort, ['new', 'top'])) {
            $sort = 'new';
        }

        $qb = $postRepository->createQueryBuilder('p')
            ->where('p.game = :game')
            ->setParameter('game', $game);

        if ($sort === 'top') {
            $qb->orderBy('p.likes', 'DESC');
        } else {
            $qb->orderBy('p.createdAt', 'DESC');
        }

        $posts = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5,
            [
                'pageParameterName' => 'page',
                'sortFieldParameterName' => null,
                'sortDirectionParameterName' => null,
                'defaultSortFieldName' => null,
                'defaultSortDirection' => null,
                'wrap-queries' => true,
            'query' => $request->query->all()
            ]
        );

        return $this->render('game/forum.html.twig', [
            'game' => $game,
            'posts' => $posts,
            'sort' => $sort
        ]);
    }
}