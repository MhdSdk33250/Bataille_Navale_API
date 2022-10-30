<?php

namespace App\Service;

use App\Entity\Boat;
use App\Entity\Game;
use App\Entity\Player;

use App\Entity\Fleet;
use Psr\Log\LoggerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class GameService
{

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->gameRepository = $doctrine->getRepository(Game::class);
        $this->playerRepository = $doctrine->getRepository(Player::class);
    }

    public function placeBoats(Game $game)
    {
        $game->setGameState("placing");
        $numberOfBoats = $game->getNumberOfBoats();
        $players = $game->getPlayers();
        $player1 = $players[0];
        $fleet1 = new Fleet();

        $fleet1->setStatus(true);
        $game->addFleet($fleet1);
        for ($i = 0; $i < $numberOfBoats; $i++) {
            $boat = new Boat();
            $boat->setPosX(0)
                ->setPosY(0);
            $fleet1->addBoat($boat);
            $this->em->persist($boat);
        }
        $player1->setFleet($fleet1);
        $this->em->persist($fleet1);
        $player2 = $players[1];
        $fleet2 = new Fleet();

        $fleet2->setStatus(true);
        $game->addFleet($fleet2);
        for ($i = 0; $i < $numberOfBoats; $i++) {
            $boat = new Boat();
            $boat->setPosX(0)
                ->setPosY(0);
            $fleet2->addBoat($boat);
            $this->em->persist($boat);
        }
        $player2->setFleet($fleet2);
        $this->em->persist($fleet2);
        $this->em->flush();
    }
}
