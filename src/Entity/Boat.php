<?php

namespace App\Entity;

use App\Repository\BoatRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BoatRepository::class)]
class Boat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getGame'])]
    private ?int $id = null;
    #[Groups(['getGame'])]
    #[ORM\Column(nullable: true)]
    private ?int $posX = null;
    #[Groups(['getGame'])]
    #[ORM\Column(nullable: true)]
    private ?int $posY = null;
    #[ORM\ManyToOne(inversedBy: 'boats')]
    private ?Fleet $fleet = null;

    #[ORM\Column]
    #[Groups(['getGame'])]
    private ?bool $status = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosX(): ?int
    {
        return $this->posX;
    }

    public function setPosX(?int $posX): self
    {
        $this->posX = $posX;

        return $this;
    }

    public function getPosY(): ?int
    {
        return $this->posY;
    }

    public function setPosY(?int $posY): self
    {
        $this->posY = $posY;

        return $this;
    }

    public function getFleet(): ?Fleet
    {
        return $this->fleet;
    }

    public function setFleet(?Fleet $fleet): self
    {
        $this->fleet = $fleet;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
