<?php

namespace App\Entity;

use Hateoas\Configuration\Annotation as Hateoas;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PlayerRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * create all hateoas relations
 * @Hateoas\Relation(
 * "self",
 * href = @Hateoas\Route(
 * "player.get",absolute = true,
 * parameters = { "idPlayer" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getPlayer")
 * ),
 * @Hateoas\Relation(
 * "up",
 * href = "http://127.0.0.1:8000/api/player/",
 * exclusion = @Hateoas\Exclusion(groups = "getPlayer")
 * )
 * @Hateoas\Relation(
 * "collection",
 * href = @Hateoas\Route(
 * "get.players",absolute = true,
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getPlayer")
 * ),
 * @Hateoas\Relation(
 * "update",
 * href = @Hateoas\Route(
 * "player.edit",absolute = true,
 * parameters = { "idPlayer" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getPlayer")
 * ),
 * 
 * @Hateoas\Relation(
 * "remove",
 * href = @Hateoas\Route(
 * "player.delete",absolute = true,
 * parameters = { "idPlayer" = "expr(object.getId())" },
 * ),
 * exclusion = @Hateoas\Exclusion(groups = "getPlayer")
 * ),
 * )
 */

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['getGame', 'getPlayer'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getGame', 'getPlayer'])]
    private ?string $username = null;
    #[ORM\Column(nullable: true)]
    private ?array $roles = [];
    /**
     * @var string The hashed password
     */
    #[Groups(['getGame', 'getPlayer'])]
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getGame', 'getPlayer'])]
    private $imageFile;
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getGame', 'getPlayer'])]
    private ?string $imagePath;
    #[ORM\ManyToOne(inversedBy: 'players')]
    private ?Game $game = null;

    #[ORM\Column]
    private ?bool $status = null;
    #[Groups(['getGame', 'getPlayer'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Fleet $fleet = null;


    public function __construct()
    {
        $this->boats = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setImageFile(?String $imageFile = null): ?Player
    {
        $this->imageFile = $imageFile;
        return $this;
    }

    public function getImageFile(): ?String
    {
        return $this->imageFile;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

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
}
