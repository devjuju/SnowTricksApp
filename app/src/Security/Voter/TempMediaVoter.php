<?php

namespace App\Security\Voter;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TempMediaVoter extends Voter
{
    public const DELETE = 'TEMP_MEDIA_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Le subject est juste le nom du fichier temporaire (string)
        return $attribute === self::DELETE && is_array($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Users) {
            return false;
        }

        [$filename, $userTempImages] = $subject;

        // L'utilisateur peut supprimer seulement si le fichier est dans sa session
        return in_array($filename, $userTempImages, true);
    }
}
