<?php

namespace App\Controller;


use App\Entity\Game;
use App\Entity\Shot;
use App\Entity\Player;
use App\Service\GameService;
use OpenApi\Annotations\Tag;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializerInterface as SerializerSerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
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
    // route to get all shots
    /**
     * @Route("/api/shots", name="get_shots", methods={"GET"})
     * @Tag(name="Shots")
     * @Security(name="Bearer")
     */
    public function getShots(SerializerInterface $serializer)
    {
        $playerId = $this->getUser()->getId();
        $playerRepository = $this->playerRepository;
        $currentPlayer = $playerRepository->find($playerId);
        // get game
        $game = $currentPlayer->getGame();
        // get oponent from game service
        $oponent = $this->gameService->getOpponent($currentPlayer);
        // get oponent fleet
        $oponentFleet = $oponent->getFleet();
        // get shots
        $shots = $oponentFleet->getShots();
        // serialize shots
        $jsonShots = $serializer->serialize($shots, 'json', ['groups' => 'shot']);
        // return response
        return new Response($jsonShots, 200, ['Content-Type' => 'application/json']);
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
    public function shoot(UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine, Request $request, SerializerSerializerInterface $serializer): JsonResponse
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
            $response = $serializer->serialize(['message' => 'You are not in a game'], 'json');
            return new JsonResponse($response, Response::HTTP_BAD_REQUEST);
        }

        // if it's not your turn
        if ($game->getWichTurn() != $currentPlayer->getId()) {
            return new JsonResponse($serializer->serialize(['message' => 'It is not your turn'], 'json'), 400, [], true);
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
                    // return serialized response

                }
                return new JsonResponse($serializer->serialize([
                    'message' => 'Hit !',
                    'remaining ennemy Boats' => $this->gameService->getRemainingBoatsNumber($opponentFleet),
                ], 'json'), 200, [], true);
            }
        }
        $shot->setHit(false);
        $this->em->persist($shot);
        $game->setWichTurn($opponent->getId());
        $this->em->persist($game);
        $this->em->flush();
        // return missed
        return new JsonResponse($serializer->serialize(['message' => 'Missed !', 'remaining ennemy Boats' => $this->gameService->getRemainingBoatsNumber($opponentFleet)], 'json'), 400, [], true);
    }
}
