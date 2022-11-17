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
 * "game.get",absolute = true,
 * parameters = { "idGame" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 * @Hateoas\Relation(
 * "up",
 * href = "http://127.0.0.1:8000/api/game/",
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 * @Hateoas\Relation(
 * "collection",
 * href = @Hateoas\Route(
 * "get.games",absolute = true,
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * ),
 * @Hateoas\Relation(
 * "config",
 * href = @Hateoas\Route(
 * "game.config",absolute = true,
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 * @Hateoas\Relation(
 * "join",
 * href = @Hateoas\Route(
 * "game.join",absolute = true,
 * parameters = { "codeGame" = "expr(object.getGameCode())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 * @Hateoas\Relation(
 * "create",
 * href = @Hateoas\Route(
 * "game.create",absolute = true,
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * )
 * @Hateoas\Relation(
 * "leave",
 * href = @Hateoas\Route(
 * "game.leave",absolute = true,
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getGame")
 * ),
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
    private ?int $numberOfBoats = 3;
    #[Groups(['getGame'])]
    #[ORM\OneToMany(mappedBy: 'game', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;


    #[ORM\Column(length: 255)]
    private ?string $gameState = "Standby";
    #[Groups(['getGame'])]
    #[ORM\Column(nullable: true)]
    private ?int $fleetDimension = 10;
    #[Groups(['getGame'])]
    #[ORM\Column(nullable: true)]
    private ?int $wichTurn = null;

    #[ORM\Column(nullable: true)]
    private ?int $winner = null;


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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getGameState(): ?string
    {
        return $this->gameState;
    }

    public function setGameState(string $gameState): self
    {
        $this->gameState = $gameState;

        return $this;
    }

    public function getFleetDimension(): ?int
    {
        return $this->fleetDimension;
    }

    public function setFleetDimension(?int $fleetDimension): self
    {
        $this->fleetDimension = $fleetDimension;

        return $this;
    }

    public function getWichTurn(): ?int
    {
        return $this->wichTurn;
    }

    public function setWichTurn(int $wichTurn): self
    {
        $this->wichTurn = $wichTurn;

        return $this;
    }

    public function getWinner(): ?int
    {
        return $this->winner;
    }

    public function setWinner(?int $winner): self
    {
        $this->winner = $winner;

        return $this;
    }
}
