<?php

namespace App\Entity;

use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\GameRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Hateoas\Relation(
 * "self",
 * href = @Hateoas\Route(
 * "game.get",
 * parameters = { "idGame" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 */
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
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    #[Groups(['getGame'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Fleet::class)]
    private Collection $fleet;


    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->fleet = new ArrayCollection();
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, fleet>
     */
    public function getFleet(): Collection
    {
        return $this->fleet;
    }

    public function addFleet(fleet $fleet): self
    {
        if (!$this->fleet->contains($fleet)) {
            $this->fleet->add($fleet);
            $fleet->setGame($this);
        }

        return $this;
    }

    public function removeFleet(fleet $fleet): self
    {
        if ($this->fleet->removeElement($fleet)) {
            // set the owning side to null (unless already changed)
            if ($fleet->getGame() === $this) {
                $fleet->setGame(null);
            }
        }

        return $this;
    }
}
