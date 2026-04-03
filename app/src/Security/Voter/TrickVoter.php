<?php

namespace App\Security\Voter;

use App\Entity\Tricks;
use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TrickVoter extends Voter
{
    public const EDIT       = 'TRICK_EDIT';
    public const DELETE     = 'TRICK_DELETE';
    public const CONTRIBUTE = 'TRICK_CONTRIBUTE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::DELETE,
            self::CONTRIBUTE
        ], true) && $subject instanceof Tricks;
    }

    protected function voteOnAttribute(string $attribute, mixed $trick, TokenInterface $token): bool
    {
        $user = $token->getUser();

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

    private function canEdit(Tricks $trick, Users $user): bool
    {
        return $this->isAuthor($trick, $user);
    }

    private function canDelete(Tricks $trick, Users $user): bool
    {
        return $this->isAuthor($trick, $user);
    }

    private function canContribute(Tricks $trick, Users $user): bool
    {
        // 👉 Ici tu peux évoluer facilement plus tard (ex: banni, privé, etc.)
        return true;
    }

    // =========================
    // 🔍 HELPERS FACTORISÉS
    // =========================

    private function isAuthor(Tricks $trick, Users $user): bool
    {
        if (!$trick->getUser()) {
            return false;
        }

        return $trick->getUser()->getId() === $user->getId();
    }
}
