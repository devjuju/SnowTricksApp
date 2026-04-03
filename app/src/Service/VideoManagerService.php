<?php

namespace App\Service;

use App\Entity\Videos;
use App\Entity\Tricks;

class VideoManagerService
{
    /**
     * @return array{videos: Videos[], errors: string[]}
     */
    public function createFromUrls(array $urls, Tricks $trick): array
    {
        $videos = [];
        $errors = [];

        foreach ($urls as $index => $url) {

            if (!is_string($url)) {
                continue;
            }

            $url = trim($url);
            if ($url === '') {
                continue;
            }

            $video = new Videos();
            $video->setUrl($url);

            $youtubeId = $video->getYoutubeId();

            // ⚠️ on ne bloque PLUS la création
            if (!$youtubeId) {
                $errors[] = "Vidéo #" . ($index + 1) . " invalide (URL non YouTube)";
                continue;
            }

            // ❌ éviter doublons seulement si ID valide
            $exists = $trick->getVideos()->exists(
                fn($i, $v) => $v->getYoutubeId() === $youtubeId
            );

            if ($exists) {
                continue;
            }
        }

        return [
            'videos' => $videos,
            'errors' => $errors,
        ];
    }
}
