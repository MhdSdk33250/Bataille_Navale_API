<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PlayerController extends AbstractController
{
    protected PlayerRepository $playerRepository;
    protected EntityManagerInterface $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->playerRepository = $doctrine->getRepository(Player::class);
    }
    /**
     * Get all players
     */
    #[Route('/api/players', name: 'get.players', methods: ['GET'])]
    public function getPlayers(SerializerInterface $serializer): JsonResponse
    {
        $players = $this->playerRepository->findAll();
        $jsonPlayers = $serializer->serialize($players, 'json');
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, ['accept' => 'json'], true);
    }
    /**
     * Get player found by id
     */
    #[Route('/api/player/{idPlayer}', name: 'player.get', methods: ['GET'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function player(Player $player, SerializerInterface $serializer): JsonResponse
    {
        $jsonPlayer = $serializer->serialize($player, 'json');
        return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
    }
    /**
     * Delete player by id
     */
    #[Route('/api/player/{idPlayer}', name: 'player.delete', methods: ['DELETE'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function deletePlayer(Player $player): JsonResponse
    {
        $this->playerRepository->remove($player);
        $this->em->flush();
        $jsonResponse = $this->json([
            'message' => 'Player deleted',
        ]);
        return new JsonResponse($jsonResponse, Response::HTTP_NO_CONTENT);
    }
    /**
     * Edit player by id
     */
    #[Route('/api/player/{idPlayer}', name: 'player.edit', methods: ['PATCH'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function updatePlayer(Request $request, Player $player, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $updatePlayer = $serializer->deserialize($request->getContent(), Player::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $player]);

        $this->em->persist($updatePlayer);
        $this->em->flush();
        $jsonPlayer = $serializer->serialize($updatePlayer, 'json', ['groups' => 'getPlayers']);
        $location = $urlGenerator->generate('player.get', ['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonPlayer, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Register new player
     */
    #[Route('/api/register', name: 'player.create', methods: ['POST'])]
    public function createPlayer(Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {

        $player = $serializer->deserialize($request->getContent(), Player::class, 'json');
        $plaintextPassword = $player->getPassword();
        // $errors = $validator->validate($player); // TODO Bryan
        // if($errors->count() > 0) {
        //     return new JsonResponse($serializer->serialize($errors,'json'), Response::HTTP_BAD_REQUEST, [], true);
        // }
        $plaintextPassword = $player->getPassword();
        $hashedPassword = $passwordHasher->hashPassword(
            $player,
            $plaintextPassword
        );
        $player->setPassword($hashedPassword);
        $this->em->persist($player);
        $this->em->flush();
        $jsonPlayer = $serializer->serialize($player, 'json');
        $location = $urlGenerator->generate('player.get', ['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonPlayer, Response::HTTP_CREATED, ["Location" => $location], true);
    }
}
