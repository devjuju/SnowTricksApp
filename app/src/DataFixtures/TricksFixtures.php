<?php

namespace App\DataFixtures;

use App\Entity\Tricks;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

// Jeux de données représentant une communauté active de snowboardeurs
// Chaque trick est associé à un utilisateur différent pour simuler un site collaboratif

class TricksFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private SluggerInterface $slugger) {}

    public function load(ObjectManager $manager): void
    {
        $tricksData = [
            ['title' => 'Mute', 'content' => 'Grab de la planche avec la main avant sur le pied avant, légèrement différent du Melon, très élégant.', 'image' => 'mute.jpg',        'category' => 'Spins',  'user' => 'JimmySweat'],

            ['title' => 'Nos grab',    'content' => 'Saisie du nez de la planche avec la main avant, idéal pour les rotations aériennes stylées.', 'image' => 'nos_grab.jpg',    'category' => 'Spins',  'user' => 'JimmySweat'],

            ['title' => 'Backflip',    'content' => 'Un salto arrière complet en l’air, idéal pour impressionner sur les sauts.', 'image' => 'backflip.jpg',    'category' => 'Flips',  'user' => 'JimmySweat'],

            ['title' => 'Buntslide',   'content' => 'Glisse rapide sur un rail ou une boîte avec le pied avant en premier, demandant équilibre et précision.', 'image' => 'buntslide.jpg',   'category' => 'Rails',  'user' => 'JimmySweat'],

            ['title' => 'Rodeo',       'content' => 'Rotation combinée avec un flip latéral, un trick avancé pour les sauteurs expérimentés.', 'image' => 'rodeo.jpg',       'category' => 'Flips',  'user' => 'SnowFox'],

            ['title' => 'Frontflip',   'content' => 'Un salto avant complet, spectaculaire et technique, souvent réalisé sur kicker ou tremplin.', 'image' => 'frontflip.jpg',   'category' => 'Flips',  'user' => 'SnowFox'],

            ['title' => 'Frontside',   'content' => 'Rotation à 180° ou plus, les épaules et le front orientés vers la direction de la rotation.', 'image' => 'frontside.jpg',   'category' => 'Spins',  'user' => 'ShredMaster'],

            ['title' => 'Indy',        'content' => 'Saisie de la planche entre les jambes sur le pied arrière, classique pour les grabs aériens.', 'image' => 'indy.jpg',        'category' => 'Grabs',  'user' => 'ShredMaster'],

            ['title' => 'Nose grab',   'content' => 'Attraper le nez de la planche en plein saut, ajoute style et contrôle.', 'image' => 'nose_grab.jpg',   'category' => 'Grabs',  'user' => 'JimmySweat'],

            ['title' => 'Cork',        'content' => 'Rotation décalée hors axe, combinant flip et rotation, très impressionnant visuellement.', 'image' => 'cork.jpg',        'category' => 'Rails',  'user' => 'MountainSoul'],

            ['title' => 'Corce',       'content' => 'Rotation aérienne complexe, un peu comme le Cork, demandant coordination et précision.', 'image' => 'corce.jpg',       'category' => 'Spins',  'user' => 'MountainSoul'],
            ['title' => 'Tail grab',   'content' => 'Saisie de la queue de la planche en plein saut, pour le style et le contrôle en l’air.', 'image' => 'tail_grab.jpg',   'category' => 'Grabs',  'user' => 'JimmySweat'],

            ['title' => 'Stalefish',   'content' => 'Grab classique où la main arrière attrape le côté du pied arrière et la planche, très stylé.', 'image' => 'stalefish.jpg',   'category' => 'Spins',  'user' => 'JimmySweat'],

            ['title' => 'Backside',    'content' => 'Rotation avec le dos orienté vers la direction de rotation, souvent 180° ou plus.', 'image' => 'backside.jpg',    'category' => 'Spins',  'user' => 'MountainSoul'],

            ['title' => 'Melon',       'content' => 'Saisie de la planche avec la main avant sur le côté du pied avant, un grab classique.', 'image' => 'melon.jpg',       'category' => 'Spins',  'user' => 'MountainSoul'],

        ];

        foreach ($tricksData as $data) {
            $trick = new Tricks();
            $trick->setTitle($data['title']);
            $trick->setSlug(strtolower($this->slugger->slug($data['title'])));
            $trick->setContent($data['content']);
            $trick->setFeaturedImage($data['image']);
            $trick->setCategory($this->getReference(
                $data['category'],
                \App\Entity\Categories::class
            ));
            $trick->setUser($this->getReference(
                $data['user'],
                \App\Entity\Users::class
            ));

            $manager->persist($trick);

            $ref = 'trick_' . strtolower(str_replace(' ', '_', $data['title']));
            $this->addReference($ref, $trick);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            CategoriesFixtures::class,
        ];
    }
}
