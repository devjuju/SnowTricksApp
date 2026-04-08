<?php

namespace App\Security\Voter;

use App\Entity\Images;
use App\Entity\Videos;
use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Gère les autorisations sur les médias (Images / Videos)
 *
 * Règles :
 * - EDIT : uniquement le propriétaire du média
 * - DELETE : le propriétaire OU l’auteur du Trick associé
 */
class MediaVoter extends Voter
{
    // Actions disponibles pour les médias
    public const EDIT   = 'MEDIA_EDIT';
    public const DELETE = 'MEDIA_DELETE';

    public function __construct(private Security $security) {}

    /**
     * Vérifie si ce voter est concerné par l’attribut et le sujet donnés
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $this->isMedia($subject);
    }

    /**
     * Point d’entrée de la décision d’autorisation
     */
    protected function voteOnAttribute(string $attribute, mixed $media, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Refuse l'accès si l'utilisateur n'est pas authentifié
        if (!$user instanceof Users) {
            return false;
        }

        return match ($attribute) {
            self::EDIT   => $this->canEdit($media, $user),
            self::DELETE => $this->canDelete($media, $user),
            default      => false,
        };
    }

    // =========================
    // 🧠 LOGIQUE MÉTIER
    // =========================

    /**
     * Autorisation de modification :
     * uniquement le propriétaire du média
     */
    private function canEdit(Images|Videos $media, Users $user): bool
    {
        return $this->isOwner($media, $user);
    }

    /**
     * Autorisation de suppression :
     * - propriétaire du média
     * - OU auteur du Trick associé
     */
    private function canDelete(Images|Videos $media, Users $user): bool
    {
        return $this->isOwner($media, $user)
            || $this->isAuthorOfTrick($media, $user);
    }

    // =========================
    // 🔍 HELPERS FACTORISÉS
    // =========================

    /**
     * Vérifie que le sujet est bien un média valide
     */
    private function isMedia(mixed $subject): bool
    {
        return $subject instanceof Images || $subject instanceof Videos;
    }

    /**
     * Vérifie si l'utilisateur est propriétaire du média
     * (via le Trick associé au média)
     */
    private function isOwner(Images|Videos $media, Users $user): bool
    {
        return $media->getTrick()?->getUser()?->getId() === $user->getId();
    }

    /**
     * Vérifie si l'utilisateur est l’auteur du Trick associé au média
     */
    private function isAuthorOfTrick(Images|Videos $media, Users $user): bool
    {
        $trick = $media->getTrick();

        if (!$trick || !$trick->getUser()) {
            return false;
        }

        return $trick->getUser()->getId() === $user->getId();
    }
}
