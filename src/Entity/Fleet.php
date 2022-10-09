<?php

namespace App\Entity;

use App\Repository\FleetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FleetRepository::class)]
class Fleet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?int $fleetDimensionX = null;

    #[ORM\Column]
    private ?int $fleetDimensionY = null;

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
}
