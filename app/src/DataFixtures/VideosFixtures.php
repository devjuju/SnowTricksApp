<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Videos;

class VideosFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $videosData = [
            'trick_nos_grab' => [
                'https://youtu.be/hW_RhD0D-Ew?si=eme94s6Xtl-6Q3Xu',
            ],
            'trick_mute' => [
                'https://youtu.be/mBB7CznvSPQ?si=BQ5lIYO8Z1YBE7Cc',
            ],
            'trick_backside' => [
                'https://youtu.be/XKoj-e52w30?si=EFYIeQNFFBVu5q4p',
            ],
            'trick_frontside' => [
                'https://youtu.be/pJxmL9uh27c?si=VFucBvk_TtDzl7Dc',
            ]

        ];

        foreach ($videosData as $trickRef => $urls) {
            /** @var \App\Entity\Tricks $trick */
            $trick = $this->getReference($trickRef, \App\Entity\Tricks::class);

            foreach ($urls as $url) {
                $video = new Videos();
                $video->setUrl($url);
                $video->setTrick($trick);
                $video->setUser($trick->getUser()); // ✅ setter corrigé

                $manager->persist($video);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TricksFixtures::class,
        ];
    }
}
