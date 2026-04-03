<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class YoutubeUrlToIdTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        // entity → form
        return $value ? "https://youtu.be/$value" : '';
    }

    public function reverseTransform($value): ?string
    {
        if (!$value) return null;

        if (preg_match('/(?:youtu\.be\/|v=|shorts\/)([a-zA-Z0-9_-]{11})/', $value, $m)) {
            return $m[1];
        }

        return null;
    }
}
