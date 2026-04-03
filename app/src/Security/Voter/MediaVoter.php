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

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE], true)
            && $this->isMedia($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $media, TokenInterface $token): bool
    {
        $user = $token->getUser();

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

    private function canEdit(Images|Videos $media, Users $user): bool
    {
        return $this->isOwner($media, $user);
    }

    private function canDelete(Images|Videos $media, Users $user): bool
    {
        return $this->isOwner($media, $user)
            || $this->isAuthorOfTrick($media, $user);
    }

    // =========================
    // 🔍 HELPERS FACTORISÉS
    // =========================

    private function isMedia(mixed $subject): bool
    {
        return $subject instanceof Images || $subject instanceof Videos;
    }

    private function isOwner(Images|Videos $media, Users $user): bool
    {
        return $media->getTrick()?->getUser()?->getId() === $user->getId();
    }

    private function isAuthorOfTrick(Images|Videos $media, Users $user): bool
    {
        $trick = $media->getTrick();

        if (!$trick || !$trick->getUser()) {
            return false;
        }

        return $trick->getUser()->getId() === $user->getId();
    }
}
