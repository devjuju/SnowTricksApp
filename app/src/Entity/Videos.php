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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $url = null;

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
    // CMS HELPERS
    // -------------------------

    public function getIdentifier(): string
    {
        return $this->youtubeId ?? (string) $this->id;
    }

    public function getEmbedUrl(): ?string
    {
        return $this->youtubeId
            ? "https://www.youtube.com/embed/{$this->youtubeId}"
            : null;
    }

    public function getWatchUrl(): ?string
    {
        return $this->youtubeId
            ? "https://www.youtube.com/watch?v={$this->youtubeId}"
            : null;
    }

    public function isValid(): bool
    {
        return !empty($this->youtubeId);
    }

    // -------------------------
    // 🎬 YOUTUBE PARSER (GRADE A)
    // -------------------------

    public static function extractYoutubeId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $query = parse_url($url, PHP_URL_QUERY) ?? '';

        $id = match (true) {
            self::isShortUrl($host) => self::extractFromShortUrl($path),
            self::isYoutubeDomain($host) => self::extractFromQuery($query),
            self::isShortsUrl($path) => self::extractFromShorts($path),
            default => null,
        };

        return self::isValidYoutubeId($id) ? $id : null;
    }

    // -------------------------
    // 🔧 HELPERS (SINGLE RESPONSIBILITY)
    // -------------------------

    private static function isShortUrl(string $host): bool
    {
        return str_contains($host, 'youtu.be');
    }

    private static function isYoutubeDomain(string $host): bool
    {
        return str_contains($host, 'youtube.com');
    }

    private static function isShortsUrl(string $path): bool
    {
        return str_contains($path, '/shorts/');
    }

    private static function extractFromShortUrl(string $path): ?string
    {
        $segments = explode('/', trim($path, '/'));
        return $segments[0] ?? null;
    }

    private static function extractFromShorts(string $path): ?string
    {
        $segments = explode('/', trim($path, '/'));
        $index = array_search('shorts', $segments, true);

        return ($index !== false && isset($segments[$index + 1]))
            ? $segments[$index + 1]
            : null;
    }

    private static function extractFromQuery(string $query): ?string
    {
        foreach (explode('&', $query) as $param) {
            if (str_starts_with($param, 'v=')) {
                return substr($param, 2);
            }
        }

        return null;
    }

    private static function isValidYoutubeId(?string $id): bool
    {
        return is_string($id)
            && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) === 1;
    }

    // -------------------------
    // STATIC HELPERS
    // -------------------------

    public static function getEmbedFromUrl(string $url): ?string
    {
        $id = self::extractYoutubeId($url);

        return $id ? "https://www.youtube.com/embed/$id" : null;
    }
}
