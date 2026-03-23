<?php

namespace App\Security\Voter;

use App\Entity\Images;
use App\Entity\Videos;
use App\Entity\Users;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaVoter extends Voter
{
    public const EDIT   = 'MEDIA_EDIT';
    public const DELETE = 'MEDIA_DELETE';

    public function __construct(private Security $security) {}

    /**
     * Détermine si cet attribut et ce sujet sont pris en charge
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && ($subject instanceof Images || $subject instanceof Videos);
    }

    /**
     * Logique de vote
     */
    protected function voteOnAttribute(string $attribute, mixed $media, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Sécurité : si l'utilisateur n'est pas connecté ou n'est pas un Users, on refuse
        if (!$user instanceof Users) {
            return false;
        }

        // -------------------
        // Vérification propriétaire
        // -------------------
        // Le propriétaire du média
        $isOwnerOfMedia = $media->getUser()?->getId() === $user->getId();

        // L’auteur de la Trick associée au média
        $isAuthorOfTrick = $media->getTrick()?->getUser()?->getId() === $user->getId();

        // -------------------
        // Retour du droit selon l'action
        // -------------------
        return match ($attribute) {

            // Seul le propriétaire du média peut le modifier
            self::EDIT => $isOwnerOfMedia,

            // Suppression : propriétaire du média OU auteur du trick
            self::DELETE => $isOwnerOfMedia || $isAuthorOfTrick,

            default => false,
        };
    }
}
