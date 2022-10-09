<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PlayerController extends AbstractController
{
    protected PlayerRepository $playerRepository;
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $doctrine){
        $this->em = $entityManager;
        $this->playerRepository = $doctrine->getRepository(Player::class);
    }

    #[Route('/api/players', name: 'get.players', methods: ['GET'])]
    public function getPlayers(SerializerInterface $serializer): JsonResponse
    {
        $players = $this->playerRepository->findAll();
        $jsonPlayers = $serializer->serialize($players, 'json');
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/player/{idPlayer}', name: 'player.get', methods: ['GET'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class:'App\Entity\Player')]
    public function player(Player $player, SerializerInterface $serializer): JsonResponse
    {
        $jsonPlayer = $serializer->serialize($player, 'json');
        return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
