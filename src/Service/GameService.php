<?php

namespace App\Service;

use App\Entity\Game;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use Gedmo\Sluggable\Util\Urlizer;

class GameService
{
    private $logger;
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }
    public function placeBoats(Game $game)
    {
        $game->setGameState("placing");
        // get number of boats from game
        $numberOfBoats = $game->getNumberOfBoats();
    }
}
