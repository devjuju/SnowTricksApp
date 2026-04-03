<?php

namespace App\Service;

use App\Entity\Videos;

class YoutubeVideoFactory
{
    public function createFromUrl(string $url): ?Videos
    {
        $video = new Videos();
        $video->setUrl($url);

        return $video->getYoutubeId()
            ? $video
            : null;
    }
}
