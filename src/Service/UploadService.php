<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use Gedmo\Sluggable\Util\Urlizer;

class UploadService
{
    private $logger;
    private $projectDir;
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }
    public function upload($uploadDir, $file, )
    {
        try {
            $newFilename = Urlizer::urlize($file->getFilename()).'-'.uniqid().'.'.$file->guessExtension();
            $file->move($uploadDir, $newFilename);
            return $this->projectDir."\\public\\images\\players-images\\".$newFilename;
        } catch (FileException $e){

            $this->logger->error('failed to upload image: ' . $e->getMessage());
            throw new FileException('Failed to upload file');
        }
    }
}