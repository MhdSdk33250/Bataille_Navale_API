<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Service\GameService;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/api/fleet', name: 'fleet.get', methods: ['GET'])]
    public function getFleet(SerializerInterface $serializer): JsonResponse
    {
        $fleet = $this->getUser()->getFleet();
        $context = SerializationContext::create()->setGroups(['getGame']);
        $data = $serializer->serialize($fleet, 'json', $context);
        return new JsonResponse($data, 200, [], true);
    }

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
}
