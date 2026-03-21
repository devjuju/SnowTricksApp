<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d’utilisateur est déjà utilisé.')]
#[ORM\HasLifecycleCallbacks]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email]
    private string $email;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: "Le nom d’utilisateur est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom d’utilisateur doit faire au moins {{ limit }} caractères.",
        maxMessage: "Le nom d’utilisateur ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9]+$/',
        message: 'Le nom d’utilisateur ne peut contenir que des lettres et des chiffres, sans espaces ni tirets.'
    )]
    private string $username;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Tricks::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tricks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comments::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column]
    private bool $isVerified = false;

    #[Assert\Length(min: 6, max: 4096)]
    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    // -------------------
    // USER INTERFACE
    // -------------------
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
    public function eraseCredentials(): void {}

    // -------------------
    // GETTERS / SETTERS
    // -------------------
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique(array_merge($this->roles, ['ROLE_USER', 'ROLE_MEMBER']));
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $username): self
    {
        $this->username = mb_strtolower(trim($username));
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }
    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    // -------------------
    // RELATIONS
    // -------------------
    public function getTricks(): Collection
    {
        return $this->tricks;
    }
    public function addTrick(Tricks $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks->add($trick);
            $trick->setUser($this);
        }
        return $this;
    }
    public function removeTrick(Tricks $trick): self
    {
        if ($this->tricks->removeElement($trick) && $trick->getUser() === $this) {
            $trick->setUser(null);
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    // -------------------
    // LIFECYCLE CALLBACKS
    // -------------------
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlugAndAvatar(): void
    {
        if (empty($this->slug) && $this->username) {
            $this->slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $this->username)));
        }
    }
}
