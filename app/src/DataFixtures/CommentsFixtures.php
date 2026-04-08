<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Comments;

// Commentaires simulant une communauté active de snowboardeurs
// permettant de démontrer le système de pagination / "load more comments"

class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $commentsData = [
            [
                'content' => "Le Backflip est vraiment impressionnant ! J’essaie de le réussir depuis des semaines.",
                'trick' => 'trick_backflip',
                'user' => 'SnowFox'
            ],
            [
                'content' => "Très bon tuto pour le Nos Grab, les explications sont claires.",
                'trick' => 'trick_nos_grab',
                'user' => 'ShredMaster'
            ],
            [
                'content' => "Est-ce que quelqu’un a des conseils pour mieux gérer l’équilibre en l’air ?",
                'trick' => 'trick_nos_grab',
                'user' => 'MountainSoul'
            ],
            [
                'content' => "Ce trick demande vraiment de la pratique, mais le rendu est stylé.",
                'trick' => 'trick_nos_grab',
                'user' => 'JimmySweat'
            ],
            [
                'content' => "Je valide totalement, surtout en freestyle c’est un must.",
                'trick' => 'trick_nos_grab',
                'user' => 'SnowFox'
            ],
            [
                'content' => "Premier essai hier, grosse chute mais je vais persévérer 😄",
                'trick' => 'trick_nos_grab',
                'user' => 'MountainSoul'
            ],
            [
                'content' => "Le Cork est vraiment technique, respect à ceux qui le maîtrisent.",
                'trick' => 'trick_cork',
                'user' => 'MountainSoul'
            ],
        ];

        foreach ($commentsData as $data) {
            $comment = new Comments();
            $comment->setContent($data['content']);
            $comment->setTrick($this->getReference($data['trick'], \App\Entity\Tricks::class));
            $comment->setUser($this->getReference($data['user'], \App\Entity\Users::class));
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TricksFixtures::class,
            UsersFixtures::class,
        ];
    }
}
