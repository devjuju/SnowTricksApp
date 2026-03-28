<?php

namespace App\Entity;

use App\Repository\VideosRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideosRepository::class)]
class Videos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tricks $trick = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Users $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
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

    public function getMediaType(): string
    {
        return 'youtube_video';
    }

    public function getYoutubeId(): ?string
    {
        if (!$this->url) {
            return null;
        }

        $url = $this->url;
        $id = null;

        // youtu.be/ID
        if (str_contains($url, 'youtu.be')) {
            $id = trim(basename(parse_url($url, PHP_URL_PATH)));
        }

        // youtube.com/shorts/ID
        elseif (str_contains($url, '/shorts/')) {
            $id = basename(parse_url($url, PHP_URL_PATH));
        }

        // youtube.com/watch?v=ID
        elseif (str_contains($url, 'youtube.com')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $vars);
            $id = $vars['v'] ?? null;
        }

        // 🔥 VALIDATION FINALE (clé)
        return ($id && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) ? $id : null;
    }

    public function getEmbedUrl(): ?string
    {
        $id = $this->getYoutubeId();

        return $id ? sprintf('https://www.youtube.com/embed/%s', $id) : null;
    }


    #[Assert\Callback]
    public function validateYoutubeUrl(ExecutionContextInterface $context): void
    {
        if (!$this->url) {
            return; // champ optionnel
        }

        if (!$this->getYoutubeId()) {
            $context->buildViolation('Lien YouTube invalide')
                ->atPath('url')
                ->addViolation();
        }
    }
}
