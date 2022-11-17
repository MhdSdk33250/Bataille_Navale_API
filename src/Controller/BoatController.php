<?php

namespace App\Controller;

use App\Entity\Boat;
use App\Entity\Game;
use App\Entity\Player;
use App\Service\GameService;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OA\RequestBody;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BoatController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine, GameService $gameService)
    {
        $this->em = $doctrine->getManager();
        $this->gameRepository = $doctrine->getRepository(Game::class);
        $this->playerRepository = $doctrine->getRepository(Player::class);
        $this->boatRepository = $doctrine->getRepository(Boat::class);
        $this->gameService = $gameService;
    }


    /**
     * route to get all boats from current player
     * @OA\Tag(name="Boats routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/boats', name: 'boat.get', methods: ['GET'])]
    public function getBoats(SerializerInterface $serializer): JsonResponse
    {
        // get current player fleet
        $playerId = $this->getUser()->getId();
        $player = $this->playerRepository->find($playerId);
        $fleet = $player->getFleet();
        $boats = $fleet->getBoats();

        $data = [];
        foreach ($boats as $boat) {
            $data[] = [
                'id' => $boat->getId(),
                'posX' => $boat->getPosX(),
                'posY' => $boat->getPosY(),
                'status' => $boat->getStatus(),
            ];
        }
        $context = SerializationContext::create()->setGroups(['getGame']);
        $data = $serializer->serialize($data, 'json', $context);
        return new JsonResponse($data, 200, [], true);
    }

    /**
     * route to place a boat from current player
     * @Security(name="Bearer")
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *      example={
     *              "boatId": "int",
     *              "posX": "int",
     *              "posY": "int"
     *         }
     *   )
     * )
     * @OA\Tag(name="Boats routes")
     */
    #[Route('/api/boat/place', name: 'boat.place', methods: ['POST'])]
    public function placeBoat(SerializerInterface $serializer, Request $request): JsonResponse
    {
        $fleet = $this->getUser()->getFleet();
        $params = json_decode($request->getContent(), true);
        // get boat id from json request

        $posX = $params['posX'];
        $posY = $params['posY'];
        $game = $this->getUser()->getGame();
        $fleetDimension = $game->getFleetDimension();
        if ($posX > $fleetDimension || $posY > $fleetDimension) {
            $json = $this->json([
                "Error" => "Boat position is out of fleet dimension",
            ]);
            return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        $boat = $this->boatRepository->getBoatById($params['boatId'], $fleet);
        $boat->setPosX($params['posX']);

        $boat->setPosY($params['posY']);

        $this->em->persist($boat);

        $this->em->flush();

        $context = SerializationContext::create()->setGroups(['getGame']);


        $json = $serializer->serialize(['message' => 'Boat placed', "updated boat" => $boat], 'json', $context);


        return new JsonResponse($json, 200, [], true);
    }
}
