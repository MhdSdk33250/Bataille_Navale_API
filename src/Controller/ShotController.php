<?php

namespace App\Controller;


use App\Entity\Game;
use App\Entity\Shot;
use App\Entity\Player;
use App\Service\GameService;
use OpenApi\Annotations\Tag;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ShotController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine, GameService $gameService)
    {
        $this->em = $doctrine->getManager();
        $this->gameRepository = $doctrine->getRepository(Game::class);
        $this->playerRepository = $doctrine->getRepository(Player::class);
        $this->shotRepository = $doctrine->getRepository(Shot::class);
        $this->gameService = $gameService;
    }
    /**
     * Create a new game with a random key to join
     *
     * Generate a new game and automatically add you as player 1 
     *
     * @Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/shot/hit', name: 'shot.hit', methods: ['POST'])]
    public function shoot(UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine, Request $request): JsonResponse
    {


        $parameters = json_decode($request->getContent(), true);
        // get current player
        $playerId = $this->getUser()->getId();
        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        if ($currentPlayer->getGame() === null) {
            // return json response
            return new JsonResponse($this->json([
                'message' => 'You are not in a game',
            ]), Response::HTTP_BAD_REQUEST);
        }


        $game = $currentPlayer->getGame();
        // if game is not started
        if ($game->getGameState() != "playing") {
            return new JsonResponse([
                'message' => 'Game is not started yet',
            ], Response::HTTP_BAD_REQUEST);
        }

        // if it's not your turn
        if ($game->getWichTurn() != $currentPlayer->getId()) {
            return new JsonResponse([
                'message' => 'It\'s not your turn',
            ], Response::HTTP_BAD_REQUEST);
        }

        // get opponent
        $opponent = $this->gameService->getOpponent($currentPlayer);
        // get opponent's boats
        $opponentFleet = $opponent->getFleet();
        foreach ($opponentFleet->getBoats() as $boat) {
            // create a new shot
            $shot = new Shot();
            $shot->setPosX($parameters['posX'])
                ->setPosY($parameters['posY'])
                ->setFleet($opponentFleet)
                ->setState(true);
            // if boat is hit

            if ($boat->getStatus() && $boat->getPosX() == $parameters['posX'] && $boat->getPosY() == $parameters['posY']) {
                $boat->setStatus(false);
                $shot->setHit(true);
                $this->em->persist($shot);
                // set game wich turn to opponent id
                // check if every ennemy boat is sunk


                $game->setWichTurn($opponent->getId());
                $this->em->persist($game);
                $this->em->flush();
                $winner = $this->gameService->checkIfGameIsOver($game);
                if ($winner == !null) {
                    $game->setGameState("finished");
                    $game->setWinner($winner->getId());
                    $this->em->persist($game);
                    $this->em->flush();
                    return new JsonResponse([
                        'message' => 'Game is over',
                        'winner' => $winner->getId(),
                    ], Response::HTTP_OK);
                }
                dd($winner);
                return new JsonResponse($this->json([
                    'message' => 'Hit !',
                ]), Response::HTTP_OK, ['accept' => 'json'], true);
            }
        }
        $shot->setHit(false);
        $this->em->persist($shot);
        $game->setWichTurn($opponent->getId());
        $this->em->persist($game);
        $this->em->flush();
        return new JsonResponse($this->json([
            'message' => 'Missed !',
            'remaining ennemy Boats' => $this->gameService->getRemainingBoatsNumber($opponentFleet),
        ]), Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
