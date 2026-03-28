<?php

namespace App\Service;

class VideosTempService
{
    private array $videos = [];

    public function add(string $url): void
    {
        $this->videos[] = $url;
    }

    public function getAll(): array
    {
        return $this->videos;
    }

    public function clear(): void
    {
        $this->videos = [];
    }

    public function remove(string $url): void
    {
        $this->videos = array_filter($this->videos, fn($v) => $v !== $url);
    }
}
