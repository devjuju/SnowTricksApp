<?php

namespace App\Entity\Media;

interface MediaIdentifierInterface
{
    /**
     * Identifiant unique utilisé côté CMS (frontend + backend)
     */
    public function getIdentifier(): string;

    /**
     * Type de média (image, video, etc.)
     */
    public function getType(): string;
}
