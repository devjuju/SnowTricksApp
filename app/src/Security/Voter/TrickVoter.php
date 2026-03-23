<?php

namespace App\Security\Voter;

use App\Entity\Tricks;
use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TrickVoter extends Voter
{
    public const EDIT        = 'TRICK_EDIT';
    public const DELETE      = 'TRICK_DELETE';
    public const CONTRIBUTE  = 'TRICK_CONTRIBUTE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::EDIT,
            self::DELETE,
            self::CONTRIBUTE
        ]) && $subject instanceof Tricks;
    }

    protected function voteOnAttribute(string $attribute, mixed $trick, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Users) {
            return false;
        }

        $isAuthor = $trick->getUser()?->getId() === $user->getId();

        return match ($attribute) {

            // Seul le propriétaire peut modifier/supprimer
            self::EDIT,
            self::DELETE => $isAuthor,

            // Tout utilisateur connecté peut contribuer
            self::CONTRIBUTE => true,

            default => false,
        };
    }
}
