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

    public function initGame(Game $game)
    {
        $game->setGameState("placing");
        $numberOfBoats = $game->getNumberOfBoats();
        $players = $game->getPlayers();
        $player1 = $players[0];
        $fleet1 = new Fleet();
        $FleetDimension = $game->getFleetDimension();
        $fleet1->setStatus(true);
        for ($i = 0; $i < $numberOfBoats; $i++) {
            $boat = new Boat();
            // set boat posy and posy random within 0 a,d fleetDimension
            $boat->setPosX(rand(0, $FleetDimension));
            $boat->setPosY(rand(0, $FleetDimension));
            $fleet1->addBoat($boat);
            $this->em->persist($boat);
        }
        $player1->setFleet($fleet1);
        $this->em->persist($fleet1);
        $player2 = $players[1];
        $fleet2 = new Fleet();

        $fleet2->setStatus(true);
        for ($i = 0; $i < $numberOfBoats; $i++) {
            $boat = new Boat();
            $boat->setPosX(rand(0, $FleetDimension));
            $boat->setPosY(rand(0, $FleetDimension));
            $fleet2->addBoat($boat);
            $this->em->persist($boat);
        }
        $player2->setFleet($fleet2);
        $this->em->persist($fleet2);
        $this->em->flush();
    }

    public function startGame(Game $game)
    {
        $game->setGameState("playing");
        $game->setWichTurn($game->getPlayers()[0]->getId());
        $this->em->flush();
    }

    // get opponent of a player
    public function getOpponent(Player $player)
    {
        $game = $player->getGame();
        $players = $game->getPlayers();
        if ($players[0]->getId() == $player->getId()) {
            return $players[1];
        } else {
            return $players[0];
        }
    }

    // get remaining boats number from fleet
    public function getRemainingBoatsNumber(Fleet $fleet)
    {
        $boats = $fleet->getBoats();
        $remainingBoats = 0;
        foreach ($boats as $boat) {
            if ($boat->getStatus()) {
                $remainingBoats++;
            }
        }
        return $remainingBoats;
    }
    // checkIfGameIsOver and return winner
    public function checkIfGameIsOver(Game $game)
    {
        $players = $game->getPlayers();
        $player1 = $players[0];
        $player2 = $players[1];
        $fleet1 = $player1->getFleet();
        $fleet2 = $player2->getFleet();
        $remainingBoats1 = $this->getRemainingBoatsNumber($fleet1);
        $remainingBoats2 = $this->getRemainingBoatsNumber($fleet2);
        if ($remainingBoats1 == 0) {
            return $player2;
        } elseif ($remainingBoats2 == 0) {
            return $player1;
        } else {
            return null;
        }
    }
}
