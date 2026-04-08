<?php

namespace App\Security\Voter;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Gère la suppression des médias temporaires (non persistés en base)
 *
 * Cas d'usage :
 * - images uploadées temporairement avant validation d’un formulaire
 * - fichiers stockés en session ou en mémoire utilisateur
 *
 * Règle :
 * - un utilisateur peut supprimer uniquement ses propres fichiers temporaires
 *
 * ⚠️ Le subject ici n'est pas une entité mais un tableau :
 * [filename, userTempImages]
 */
class TempMediaVoter extends Voter
{
    public const DELETE = 'TEMP_MEDIA_DELETE';

    /**
     * Vérifie si ce voter est applicable
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::DELETE
            && $this->isValidSubject($subject);
    }

    /**
     * Point de décision d'autorisation
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Refuse si utilisateur non authentifié
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

    /**
     * Vérifie si l'utilisateur peut supprimer le fichier temporaire
     *
     * @param array{0: string, 1: array} $subject
     */
    private function canDelete(array $subject): bool
    {
        [$filename, $userTempImages] = $subject;

        return $this->isOwnedTempFile($filename, $userTempImages);
    }

    // =========================
    // 🔍 HELPERS
    // =========================

    /**
     * Valide la structure du subject attendu :
     * - index 0 : nom du fichier (string)
     * - index 1 : liste des fichiers temporaires de l'utilisateur (array)
     */
    private function isValidSubject(mixed $subject): bool
    {
        return is_array($subject)
            && count($subject) === 2
            && is_string($subject[0])
            && is_array($subject[1]);
    }

    /**
     * Vérifie si le fichier appartient bien aux fichiers temporaires de l'utilisateur
     */
    private function isOwnedTempFile(string $filename, array $userTempImages): bool
    {
        return in_array($filename, $userTempImages, true);
    }
}
