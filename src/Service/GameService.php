<?php

namespace App\Service;

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
    public function launchGame()
    {
        $this->logger->info('Game launched');
    }
}
