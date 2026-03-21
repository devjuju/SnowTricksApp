<?php

namespace App\Entity;

use App\Repository\VideosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideosRepository::class)]
class Videos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tricks $trick = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null; // <-- singulier

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getType(): string
    {
        return 'video';
    }

    public function getYoutubeId(): ?string
    {
        if (str_contains($this->url, 'youtube.com')) {
            parse_str(parse_url($this->url, PHP_URL_QUERY), $vars);
            return $vars['v'] ?? null;
        }
        if (str_contains($this->url, 'youtu.be')) {
            return basename($this->url);
        }
        return null;
    }

    public function getEmbedUrl(): ?string
    {
        $id = $this->getYoutubeId();
        return $id ? 'https://www.youtube.com/embed/' . $id : null;
    }
}
