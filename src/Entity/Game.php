<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gameCode = null;

    #[ORM\Column]
    private ?int $numberOfBoats = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGameCode(): ?string
    {
        return $this->gameCode;
    }

    public function setGameCode(?string $gameCode): self
    {
        $this->gameCode = $gameCode;

        return $this;
    }

    public function getNumberOfBoats(): ?int
    {
        return $this->numberOfBoats;
    }

    public function setNumberOfBoats(int $numberOfBoats): self
    {
        $this->numberOfBoats = $numberOfBoats;

        return $this;
    }
}
