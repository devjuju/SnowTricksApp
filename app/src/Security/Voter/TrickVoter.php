<?php

namespace App\Security\Voter;

use App\Entity\Tricks;
use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Gère les permissions liées aux Tricks
 *
 * Règles métier :
 * - TRICK_EDIT : uniquement l'auteur du trick
 * - TRICK_DELETE : uniquement l'auteur du trick
 * - TRICK_CONTRIBUTE : autorisé à tous les utilisateurs connectés
 *
 * ⚠️ Ici, la contribution est volontairement ouverte (logique métier)
 */
class TrickVoter extends Voter
{
    // Actions disponibles sur un Trick
    public const EDIT       = 'TRICK_EDIT';
    public const DELETE     = 'TRICK_DELETE';
    public const CONTRIBUTE = 'TRICK_CONTRIBUTE';

    public function __construct(private Security $security) {}

    /**
     * Vérifie si ce voter doit intervenir pour cet attribut et ce sujet
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::DELETE,
            self::CONTRIBUTE
        ], true) && $subject instanceof Tricks;
    }

    /**
     * Point d’entrée de la décision d’autorisation
     */
    protected function voteOnAttribute(string $attribute, mixed $trick, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Refuse si l'utilisateur n'est pas authentifié
        if (!$user instanceof Users) {
            return false;
        }

        return match ($attribute) {
            self::EDIT       => $this->canEdit($trick, $user),
            self::DELETE     => $this->canDelete($trick, $user),
            self::CONTRIBUTE => $this->canContribute($trick, $user),
            default          => false,
        };
    }

    // =========================
    // 🧠 LOGIQUE MÉTIER
    // =========================

    /**
     * Édition d’un trick :
     * réservée uniquement à son auteur
     */
    private function canEdit(Tricks $trick, Users $user): bool
    {
        return $this->isAuthor($trick, $user);
    }

    /**
     * Suppression d’un trick :
     * réservée uniquement à son auteur
     */
    private function canDelete(Tricks $trick, Users $user): bool
    {
        return $this->isAuthor($trick, $user);
    }

    /**
     * Contribution à un trick :
     * accessible à tout utilisateur authentifié
     *
     * ⚠️ Règle métier volontairement ouverte
     */
    private function canContribute(Tricks $trick, Users $user): bool
    {
        return true;
    }

    // =========================
    // 🔍 HELPERS FACTORISÉS
    // =========================

    /**
     * Vérifie si l'utilisateur est l'auteur du trick
     */
    private function isAuthor(Tricks $trick, Users $user): bool
    {
        if (!$trick->getUser()) {
            return false;
        }

        return $trick->getUser()->getId() === $user->getId();
    }
}
