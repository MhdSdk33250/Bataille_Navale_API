<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\GameRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[Groups(['getGame'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[Groups(['getGame'])]
    #[ORM\Column]
    private ?bool $status = null;
    #[Groups(['getGame'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gameCode = null;
    #[Groups(['getGame'])]
    #[ORM\Column(nullable: true)]
    private ?int $numberOfBoats = null;
    #[Groups(['getGame'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: player::class)]
    private Collection $players;


    public function __construct()
    {
        $this->players = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(player $player): self
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setGame($this);
        }

        return $this;
    }

    public function removePlayer(player $player): self
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getGame() === $this) {
                $player->setGame(null);
            }
        }

        return $this;
    }
}
