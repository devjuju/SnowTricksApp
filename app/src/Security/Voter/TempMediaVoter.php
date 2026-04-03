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
        return $attribute === self::DELETE
            && $this->isValidSubject($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Users) {
            return false;
        }

        return match ($attribute) {
            self::DELETE => $this->canDelete($subject),
            default      => false,
        };
    }

    // =========================
    // 🧠 LOGIQUE MÉTIER
    // =========================

    private function canDelete(array $subject): bool
    {
        [$filename, $userTempImages] = $subject;

        return $this->isOwnedTempFile($filename, $userTempImages);
    }

    // =========================
    // 🔍 HELPERS
    // =========================

    private function isValidSubject(mixed $subject): bool
    {
        return is_array($subject)
            && count($subject) === 2
            && is_string($subject[0])
            && is_array($subject[1]);
    }

    private function isOwnedTempFile(string $filename, array $userTempImages): bool
    {
        return in_array($filename, $userTempImages, true);
    }
}
