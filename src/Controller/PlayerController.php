<?php

namespace App\Controller;

use OA\Tag;
use App\Entity\Player;
use App\Service\UploadService;
use JMS\Serializer\Serializer;
use App\Repository\PlayerRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use OA\RequestBody;

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
     * Get current player
     *
     * Return your user object
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/current', name: 'player.current', methods: ['GET'])]
    public function getCurrentPlayer(SerializerInterface $serializer): JsonResponse
    {
        $playerId = $this->getUser()->getId();
        $player = $this->playerRepository->find($playerId);
        $context = SerializationContext::create()->setGroups(['getPlayer']);
        $jsonPlayer = $serializer->serialize($player, 'json', $context);
        return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
    }
    /**
     * Get players
     *
     * Return all the players
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/players', name: 'get.players', methods: ['GET'])]
    public function getPlayers(SerializerInterface $serializer): JsonResponse
    {
        $players = $this->playerRepository->findAll();
        $players = array_filter($players, function ($player) {
            return $player->isStatus();
        });
        $context = SerializationContext::create()->setGroups(['getPlayer']);
        $jsonPlayers = $serializer->serialize($players, 'json', $context);
        return new JsonResponse($jsonPlayers, Response::HTTP_OK, ['accept' => 'json'], true);
    }
    /**
     * Find player by id
     *
     * Return the player with the corresponding id
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/{idPlayer}', name: 'player.get', methods: ['GET'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function player(Player $player, SerializerInterface $serializer): JsonResponse
    {
        if ($player->isStatus()) {
            $context = SerializationContext::create()->setGroups(['getPlayer']);
            $jsonPlayer = $serializer->serialize($player, 'json', $context);
            return new JsonResponse($jsonPlayer, Response::HTTP_OK, ['accept' => 'json'], true);
        }
    }
    /**
     * Delete player by id
     *
     * Set
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/{idPlayer}', name: 'player.delete', methods: ['DELETE'])]
    #[ParamConverter("Player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
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
     * Update player by id
     *
     * Simply update the player with the corresponding id
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/{idPlayer}', name: 'player.edit', methods: ['PATCH'])]
    #[ParamConverter("Player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function updatePlayer(Request $request, Player $player, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $updatePlayer = $serializer->deserialize($request->getContent(), Player::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $player]);

        $this->em->persist($updatePlayer);
        $this->em->flush();
        $context = SerializationContext::create()->setGroups(['getPlayer']);
        $jsonPlayer = $serializer->serialize($updatePlayer, 'json', $context);
        $location = $urlGenerator->generate('player.get', ['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonPlayer, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Register a new player
     *
     * Return your user object
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         example={
     *             "username": "newuser",
     *             "password": "pwd"
     *         }
     *     )
     * )
     * @OA\Tag(name="Login")
     */
    #[Route('/api/register', name: 'player.create', methods: ['POST'])]
    public function createPlayer(Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {

        $player = $serializer->deserialize($request->getContent(), Player::class, 'json');
        $plaintextPassword = $player->getPassword();
        $plaintextPassword = $player->getPassword();
        $hashedPassword = $passwordHasher->hashPassword(
            $player,
            $plaintextPassword
        );
        $player->setPassword($hashedPassword);
        $player->setStatus(true);
        $this->em->persist($player);
        $this->em->flush();
        $json = $this->json([
            "message" => "User registered",
            "user url" => $urlGenerator->generate('player.get', ['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]);
        $location = $urlGenerator->generate('player.get', ['idPlayer' => $player->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    /**
     * Upload profile picture
     *
     *  Set your profile picture !
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/uploadpic', name: 'player.uploadpic', methods: ['POST'])]
    public function uploadpic(UploadService $uploader, ManagerRegistry $doctrine, Request $request, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $playerId = $this->getUser()->getId();

        $playerRepository = $doctrine->getRepository(Player::class);
        $currentPlayer = $playerRepository->find($playerId);
        $receivedFile = $request->files->get('image');
        $imagePath = $uploader->upload('images/players-images', $receivedFile);
        $imageUrl = $urlGenerator->generate('picture', ['idPlayer' => $currentPlayer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $currentPlayer->setImageFile($imageUrl);
        $currentPlayer->setImagePath($imagePath);
        $this->em->persist($currentPlayer);
        $this->em->flush();
        $jsonResponse = $this->json([
            'message' => 'Player picture updated',
            "user picture url" => $imageUrl
        ]);
        $location = $urlGenerator->generate('picture', ['idPlayer' => $currentPlayer->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonResponse, Response::HTTP_CREATED, ["Location" => $location], true);
    }



    /**
     * Get your profile picture
     *
     * Return your profile picture
     * @OA\Tag(name="Player routes")
     * @Security(name="Bearer")
     */
    #[Route('/api/player/picture/{idPlayer}', name: 'picture', methods: ['GET'])]
    #[ParamConverter("player", options: ["id" => "idPlayer"], class: 'App\Entity\Player')]
    public function getPicture(Request $request, Player $player)
    {

        $filePath = $player->getImagePath();

        if (file_exists($filePath)) {
            $response = new Response();
            $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, "image");
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', 'image/png');
            $response->setContent(file_get_contents($filePath));
            return $response;
        } else {
            return $this->redirect($this->generateUrl('my_url_to_site_index'));
        }
    }
}
