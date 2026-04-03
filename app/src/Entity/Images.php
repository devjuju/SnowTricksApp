<?php

namespace App\Entity;

use App\Repository\ImagesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
#[ORM\Table(
    name: "images",
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: "uniq_trick_image",
            columns: ["trick_id", "public_id"]
        )
    ]
)]
#[ORM\HasLifecycleCallbacks]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Comme pour youtubeId, utiliser un identifiant métier stable
    #[ORM\Column(length: 36, nullable: true)]
    private ?string $publicId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tricks $trick = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // Equivalent parfait de Videos::getIdentifier()
    public function getIdentifier(): string
    {
        return $this->publicId;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPublicId(?string $publicId): static
    {
        $this->publicId = $publicId;
        return $this;
    }

    public function setPicture(string $picture): static
    {
        $this->picture = $picture;
        return $this;
    }

    public function getTrick(): ?Tricks
    {
        return $this->trick;
    }

    public function setTrick(?Tricks $trick): static
    {
        $this->trick = $trick;
        return $this;
    }

    public function getType(): string
    {
        return 'image';
    }

    public function getPath(): ?string
    {
        return $this->picture ? '/uploads/tricks/' . $this->picture : null;
    }

    #[ORM\PreRemove]
    public function deleteFile(): void
    {
        if ($this->picture) {
            $file = __DIR__ . '/../../public/uploads/tricks/' . $this->picture;
            if (file_exists($file) && is_file($file)) unlink($file);
        }
    }

    // Générer automatiquement publicId

    #[ORM\PrePersist]
    public function generatePublicId(): void
    {
        if (!$this->publicId) {
            $this->publicId = bin2hex(random_bytes(16));
        }
    }

    // helper métier pour éviter doublons dans fixtures ou CMS
    public function isSameImage(self $other): bool
    {
        return $this->getIdentifier() === $other->getIdentifier();
    }
}
