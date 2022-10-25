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
    public function createGame($uploadDir, $file, )
    {
        try {
           
        } catch (FileException $e){

            $this->logger->error('failed to upload image: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
    }
}