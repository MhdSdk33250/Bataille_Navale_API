<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Fleet;
use App\Entity\Player;
use DateTimeImmutable;
use App\Service\GameService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class GameController extends AbstractController
{

    public function __construct(ManagerRegistry $doctrine, GameService $gameService)
    {
        $this->em = $doctrine->getManager();
        $this->gameRepository = $doctrine->getRepository(Game::class);
        $this->playerRepository = $doctrine->getRepository(Player::class);
        $this->gameService = $gameService;
    }

    #[Route('/api/game/gamestate', name: 'game.state', methods: ['GET'])]
    public function getCurrentGameState(): JsonResponse
    {
        $playerId = $this->getUser()->getId();
        $player = $this->playerRepository->find($playerId);
        $game = $player->getGame();
        if ($game) {
            $json = $this->json([
                "gameState" => $game->getGameState(),
            ]);
            return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
        } else {
            return new JsonResponse('No game found', Response::HTTP_NOT_FOUND, ['accept' => 'json'], true);
        }
    }


    #[Route('/api/game/current', name: 'game.current', methods: ['GET'])]
    public function getCurrentGame(SerializerInterface $serializer): JsonResponse
    {
        $playerId = $this->getUser()->getId();
        $player = $this->playerRepository->find($playerId);
        $game = $player->getGame();
        if ($game) {
            $context = SerializationContext::create()->setGroups(['getGame']);
            $jsonGame = $serializer->serialize($game, 'json', $context);
            return new JsonResponse($jsonGame, Response::HTTP_OK, ['accept' => 'json'], true);
        } else {
            return new JsonResponse('No game found', Response::HTTP_NOT_FOUND, ['accept' => 'json'], true);
        }
    }

    /**
     * Return specified game
     *
     * Search game with Given id and return it
     *
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('api/game/{idGame}', name: 'game.get', methods: ['GET'])]
    #[ParamConverter("game", options: ["id" => "idGame"], class: 'App\Entity\Game')]
    public function getGame(Game $game, SerializerInterface $serializer): JsonResponse
    {
        if ($game->isStatus()) {
            $context = SerializationContext::create()->setGroups(['getGame']);
            $jsonGame = $serializer->serialize($game, 'json', $context);
            return new JsonResponse($jsonGame, Response::HTTP_OK, ['accept' => 'json'], true);
        }
        return new JsonResponse($this->json(['message' => 'Game not found']), Response::HTTP_NOT_FOUND, ['accept' => 'json'], true);
    }
    /**
     * Return filtered list of game
     *
     * return filtered list of game with given date, not usefull in the game
     *
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('api/game/filter/{date}', name: 'game.filter', methods: ['GET'])]
    public function filterGame($date, SerializerInterface $serializer): JsonResponse
    {


        $games = $this->gameRepository->filterByDate($date);
        $games = array_filter($games, function ($game) {
            return $game->isStatus();
        });
        $context = SerializationContext::create()->setGroups(['getGame']);
        $jsonGames = $serializer->serialize($games, 'json', $context);
        return new JsonResponse($jsonGames, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Return a list of active games
     *
     * Generate a new game and automatically add you as player 1 
     *
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('api/games/', name: 'get.games', methods: ['GET'])]
    public function getGames(SerializerInterface $serializer): JsonResponse
    {
        $games = $this->gameRepository->findAll();
        $games = array_filter($games, function ($game) {
            return $game->isStatus();
        });
        $context = SerializationContext::create()->setGroups(['getGame']);
        $jsonGames = $serializer->serialize($games, 'json', $context);
        return new JsonResponse($jsonGames, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    /**
     * Create a new game with a random key to join
     *
     * Generate a new game and automatically add you as player 1 
     *
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/game/create', name: 'game.create', methods: ['POST'])]
    public function createGame(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine): JsonResponse
    {
        $playerId = $this->getUser()->getId();

        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        $game = new Game();

        $game->setStatus(true);
        $game->addPlayer($currentPlayer);
        $codeGame = rand(1000, 10000);

        $game->setGameCode($codeGame);
        $game->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($game);
        $this->em->flush();


        $json = $this->json([
            "message" => "Game created",
            "game code" => $codeGame,
            "game url" => $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/game/join/{codeGame}', name: 'game.join', methods: ['POST'])]
    /**
     * Join a game with the given code
     *
     * You need to be logged in to join a game, this add u to the game as a player 2
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    public function joinGame(string $codeGame, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine): JsonResponse
    {
        $game = $this->gameRepository->findOneBy(['gameCode' => $codeGame]);
        if (!$game) {
            return new JsonResponse($this->json(['message' => 'Game not found']), Response::HTTP_NOT_FOUND, ['accept' => 'json'], true);
        }
        if (count($game->getPlayers()) >= 2) {
            return new JsonResponse($this->json(['message' => 'Game is full']), Response::HTTP_BAD_REQUEST, ['accept' => 'json'], true);
        }
        $playerId = $this->getUser()->getId();
        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        $game->addPlayer($currentPlayer);
        if (count($game->getPlayers()) == 2) {
            $this->gameService->placeBoats($game);
        }



        $this->em->persist($game);
        $this->em->flush();
        $context = SerializationContext::create()->setGroups(['getGame']);
        $json = $this->json([
            'message' => 'Game successfully joined',
            "game url" => $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);

        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/game/leave', name: 'game.leave', methods: ['POST'])]
    /**
     * Leave game
     *
     * You leave the game you are currently in
     *
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    public function leaveGame(SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine): JsonResponse
    {
        $playerId = $this->getUser()->getId();
        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        $game = $currentPlayer->getGame();
        if (!$game) {
            return new JsonResponse($this->json(['message' => 'You are not in a game']), Response::HTTP_BAD_REQUEST, ['accept' => 'json'], true);
        }
        $game->removePlayer($currentPlayer);
        if ($game->getPlayers()->isEmpty()) {
            $game->setStatus(false);
        }
        $this->em->flush();
        $context = SerializationContext::create()->setGroups(['getGame']);
        $json = $this->json([
            'message' => 'Game successfully leaved',
            "leaved game" => $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Configure game
     *
     * Config the game you are currently in
     * @OA\Parameter(
     * name="numberOfBoats",
     * in="query",
     * description="number of boats each players will have",
     * @OA\Schema(type="number")
     * )
     * @OA\Parameter(
     * name="fleetDimensions",
     * in="query",
     * description="square size of the fleet",
     * @OA\Schema(type="number")
     * )
     * @OA\Tag(name="Game routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/game/config', name: 'game.config', methods: ['POST'])]
    public function configGame(Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, ManagerRegistry $doctrine): JsonResponse
    {
        $playerId = $this->getUser()->getId();
        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        $game = $currentPlayer->getGame();
        if ($game === null) {
            return new JsonResponse($this->json([
                'message' => 'You are not in a game, to join a game use the following url',
                "url" => $urlGenerator->generate('game.join', ["codeGame" => "12345"], UrlGeneratorInterface::ABSOLUTE_URL)
            ]), Response::HTTP_NOT_FOUND, ['accept' => 'json'], true);
        }

        $parameters = json_decode($request->getContent(), true);
        $game->setNumberOfBoats($parameters['numberOfBoats']  ?? 3);
        $game->setFleetDimension($parameters['fleetDimensions'] ?? 10);
        $context = SerializationContext::create()->setGroups(['getGame']);
        $jsonGame = $serializer->serialize($game, 'json', $context);
        $location = $urlGenerator->generate('game.get', ['idGame' => $game->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonGame, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
