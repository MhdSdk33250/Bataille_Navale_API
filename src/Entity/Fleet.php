<?php

namespace App\Entity;

use App\Repository\FleetRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FleetRepository::class)]
class Fleet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getGame'])]
    private ?int $id = null;
    #[Groups(['getGame'])]
    #[ORM\Column]
    private ?bool $status = null;
    #[Groups(['getGame'])]
    #[ORM\Column]
    private ?int $fleetDimensionX = null;
    #[Groups(['getGame'])]
    #[ORM\Column]
    private ?int $fleetDimensionY = null;

    #[ORM\ManyToOne(inversedBy: 'Fleet')]
    private ?Game $game = null;

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

    public function getFleetDimensionX(): ?int
    {
        return $this->fleetDimensionX;
    }

    public function setFleetDimensionX(int $fleetDimensionX): self
    {
        $this->fleetDimensionX = $fleetDimensionX;

        return $this;
    }

    public function getFleetDimensionY(): ?int
    {
        return $this->fleetDimensionY;
    }

    public function setFleetDimensionY(int $fleetDimensionY): self
    {
        $this->fleetDimensionY = $fleetDimensionY;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }
}
