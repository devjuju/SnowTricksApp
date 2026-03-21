<?php

namespace App\Entity;

use App\Repository\TricksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\Timestampable;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(
    fields: ['title'],
    message: 'Une figure avec ce titre existe déjà.',
    errorPath: 'title'
)]
#[ORM\Entity(repositoryClass: TricksRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tricks
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    /* ===================== */
    /*      RELATIONS        */
    /* ===================== */

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?Categories $category = null;

    #[ORM\OneToMany(
        mappedBy: 'trick',
        targetEntity: Images::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $images;

    #[ORM\OneToMany(
        mappedBy: 'trick',
        targetEntity: Videos::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $videos;

    #[ORM\OneToMany(
        mappedBy: 'trick',
        targetEntity: Comments::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    /* ===================== */
    /*      CONSTRUCTOR      */
    /* ===================== */

    public function __construct()
    {
        $this->images   = new ArrayCollection();
        $this->videos   = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /* ===================== */
    /*        GETTERS        */
    /* ===================== */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function getCategory(): ?Categories
    {
        return $this->category;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    /* ===================== */
    /*        SETTERS        */
    /* ===================== */

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $featuredImage;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setCategory(?Categories $category): self
    {
        $this->category = $category;
        return $this;
    }

    /* ===================== */
    /*     RELATION OPS      */
    /* ===================== */

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setTrick($this);
        }
        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getTrick() === $this) {
                $image->setTrick(null);
            }
        }
        return $this;
    }

    public function addVideo(Videos $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setTrick($this);
        }
        return $this;
    }

    public function removeVideo(Videos $video): self
    {
        if ($this->videos->removeElement($video)) {
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }
        return $this;
    }

    public function addComment(Comments $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }
        return $this;
    }

    public function removeComment(Comments $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }
        return $this;
    }

    /* ===================== */
    /* Temporary featured image for upload workflow */
    /* ===================== */
    private ?string $tmpFeaturedImage = null;

    public function getTmpFeaturedImage(): ?string
    {
        return $this->tmpFeaturedImage;
    }

    public function setTmpFeaturedImage(?string $tmpFeaturedImage): self
    {
        $this->tmpFeaturedImage = $tmpFeaturedImage;
        return $this;
    }
}
