<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Service\GameService;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\Serializer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OA\ResquestBody;
use OA\ResponseBody;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FleetController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine, GameService $gameService)
    {
        $this->em = $doctrine->getManager();
        $this->gameRepository = $doctrine->getRepository(Game::class);
        $this->playerRepository = $doctrine->getRepository(Player::class);
        $this->gameService = $gameService;
    }

    /** 
     * @Security(name="Bearer")
     * @OA\Response(
     *    response=200,
     *    description="route to get fleet",
     *    @OA\JsonContent(
     *      example={
     *        "id": "int",
     *        "status": "bool",
     *        "boats" : 
     *           {
     *              {
     *              "boatId": "int",
     *              "posX": "int",
     *              "posY": "int",
     *              "status":"bool"
     *              },
     *              {
     *              "boatId": "int",
     *              "posX": "int",
     *              "posY": "int",
     *              "status":"bool"
     *              },
     *              {
     *              "boatId": "int",
     *              "posX": "int",
     *              "posY": "int",
     *              "status":"bool"
     *              },
     *          
     *           },
     *          "comfirmed": "bool"
     *         }
     * 
     *   )
     * )
     * @OA\Tag(name="Fleet routes")
     */
    #[Route('/api/fleet', name: 'fleet.get', methods: ['GET'])]
    public function getFleet(SerializerInterface $serializer): JsonResponse
    {
        $fleet = $this->getUser()->getFleet();
        $context = SerializationContext::create()->setGroups(['getGame']);
        $data = $serializer->serialize($fleet, 'json', $context);
        return new JsonResponse($data, 200, [], true);
    }

    /**
     * route to confirm fleet
     * @OA\Tag(name="Fleet routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/fleet/comfirm', name: 'fleet.confirm', methods: ['POST'])]
    public function confirmFleet(): JsonResponse
    {
        $fleet = $this->getUser()->getFleet();
        $fleet->setComfirmed(true);
        $this->em->persist($fleet);
        $this->em->flush();
        $json = $this->json(['Message' => "Fleet comfirmed"], 200);
        return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    // check if all fleets of current game are confirmed
    #[Route('/api/fleet/allfleetconfirmed', name: 'fleet.confirmed', methods: ['GET'])]
    public function checkFleetComfirmed(SerializerInterface $serializer): JsonResponse
    {
        $game = $this->getUser()->getGame();
        $players = $game->getPlayers();
        foreach ($players as $player) {
            if ($player->getFleet() === null || !$player->getFleet()->isComfirmed()) {
                $json = $serializer->serialize(['areAllFleetsConfirmed' => false], 'json');
                return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
            } elseif ($player->getFleet() !== null && !$player->getFleet()->isComfirmed()) {
                $json = $serializer->serialize(['areAllFleetsConfirmed' => false], 'json');
                return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
            }
        }
        $json = $serializer->serialize(['areAllFleetsConfirmed' => true], 'json');
        return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/fleet/confirmed', name: 'fleet.ready', methods: ['GET'])]
    public function checkFleetReady(SerializerInterface $serializer): JsonResponse
    {
        $game = $this->getUser()->getGame();
        $playerId = $this->getUser()->getId();
        $player = $this->playerRepository->find($playerId);
        $fleet = $player->getFleet();
        $fleetConfirmed = $fleet->isComfirmed();

        $json = $serializer->serialize(['isFleetConfirmed' => $fleetConfirmed], 'json');
        return new JsonResponse($json, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
