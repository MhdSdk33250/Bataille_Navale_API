<?php

namespace App\Entity;

use App\Repository\ShotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ShotRepository::class)]
class Shot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['shot'])]
    private ?int $id = null;
    #[Groups(['shot'])]
    #[ORM\Column]
    private ?int $posX = null;
    #[Groups(['shot'])]
    #[ORM\Column]
    private ?int $posY = null;
    #[Groups(['shot'])]
    #[ORM\Column]
    private ?bool $state = null;

    #[ORM\ManyToOne(inversedBy: 'shots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fleet $Fleet = null;
    #[Groups(['shot'])]
    #[ORM\Column]
    private ?bool $hit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosX(): ?int
    {
        return $this->posX;
    }

    public function setPosX(int $posX): self
    {
        $this->posX = $posX;

        return $this;
    }

    public function getPosY(): ?int
    {
        return $this->posY;
    }

    public function setPosY(int $posY): self
    {
        $this->posY = $posY;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }
    public function getFleet(): ?Fleet
    {
        return $this->Fleet;
    }

    public function setFleet(?Fleet $Fleet): self
    {
        $this->Fleet = $Fleet;

        return $this;
    }

    public function isHit(): ?bool
    {
        return $this->hit;
    }

    public function setHit(bool $hit): self
    {
        $this->hit = $hit;

        return $this;
    }
}
