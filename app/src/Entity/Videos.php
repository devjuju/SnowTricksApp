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

    // URL brute (toujours stockée)
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $url = null;

    // ID YouTube (nullable = vidéo invalide possible)
    #[ORM\Column(length: 11, nullable: true)]
    private ?string $youtubeId = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Tricks $trick = null;



    // -------------------------
    // GETTERS / SETTERS
    // -------------------------

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
        $this->youtubeId = self::extractYoutubeId($url);

        return $this;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): static
    {
        $this->youtubeId = $youtubeId;
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

   

    // -------------------------
    // 🎬 CMS HELPERS
    // -------------------------

    /**
     * Identifiant unique utilisé pour update/delete côté CMS
     * (ULTRA IMPORTANT pour ton système front/back)
     */
    public function getIdentifier(): string
    {
        return $this->youtubeId
            ?? $this->url
            ?? (string) $this->id;
    }

    /**
     * URL embed YouTube (si valide)
     */
    public function getEmbedUrl(): ?string
    {
        return $this->youtubeId
            ? "https://www.youtube.com/embed/{$this->youtubeId}"
            : null;
    }

    /**
     * URL classique YouTube (si possible)
     */
    public function getWatchUrl(): ?string
    {
        return $this->youtubeId
            ? "https://www.youtube.com/watch?v={$this->youtubeId}"
            : null;
    }

    /**
     * Statut validité (utile UI CMS)
     */
    public function isValid(): bool
    {
        return $this->youtubeId !== null;
    }

    // -------------------------
    // 🧠 PARSER YOUTUBE
    // -------------------------

    public static function extractYoutubeId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // youtu.be/xxxx
        if (str_contains($url, 'youtu.be')) {
            $id = trim(basename(parse_url($url, PHP_URL_PATH)));
        }

        // youtube.com/watch?v=xxxx
        elseif (str_contains($url, 'youtube.com')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $vars);
            $id = $vars['v'] ?? null;
        }

        // youtube shorts
        elseif (str_contains($url, '/shorts/')) {
            $id = basename(parse_url($url, PHP_URL_PATH));
        } else {
            $id = null;
        }

        // validation finale
        return ($id && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id))
            ? $id
            : null;
    }
}
