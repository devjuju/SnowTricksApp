<?php

namespace App\Service;

final class YoutubeUrlParser
{
    public function extractId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $host = $this->getHost($url);
        $path = $this->getPath($url);
        $query = $this->getQuery($url);

        return match (true) {
            $this->isShortUrl($host) => $this->extractFromShortUrl($path),
            $this->isYoutubeDomain($host) => $this->extractFromQuery($query),
            $this->isShortsUrl($path) => $this->extractFromShorts($path),
            default => null,
        };
    }

    private function getHost(string $url): string
    {
        return parse_url($url, PHP_URL_HOST) ?? '';
    }

    private function getPath(string $url): string
    {
        return parse_url($url, PHP_URL_PATH) ?? '';
    }

    private function getQuery(string $url): string
    {
        return parse_url($url, PHP_URL_QUERY) ?? '';
    }

    private function isShortUrl(string $host): bool
    {
        return str_contains($host, 'youtu.be');
    }

    private function isYoutubeDomain(string $host): bool
    {
        return str_contains($host, 'youtube.com');
    }

    private function isShortsUrl(string $path): bool
    {
        return str_contains($path, '/shorts/');
    }

    private function extractFromShortUrl(string $path): ?string
    {
        return explode('/', trim($path, '/'))[0] ?? null;
    }

    private function extractFromShorts(string $path): ?string
    {
        $segments = explode('/', trim($path, '/'));
        $index = array_search('shorts', $segments, true);

        return ($index !== false && isset($segments[$index + 1]))
            ? $segments[$index + 1]
            : null;
    }

    private function extractFromQuery(string $query): ?string
    {
        foreach (explode('&', $query) as $param) {
            if (str_starts_with($param, 'v=')) {
                return substr($param, 2);
            }
        }

        return null;
    }
}
