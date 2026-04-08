<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Users de démonstration cohérents avec l’univers SnowTricks
// Utilisés pour simuler une communauté active en environnement de développement

class UsersFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'username' => 'JimmySweat',
                'email' => 'jimmy@snowtricks.com',
                'password' => 'Snow2025!',
                'roles' => ['ROLE_MEMBER']
            ],
            [
                'username' => 'SnowFox',
                'email' => 'snowfox@tricks.com',
                'password' => 'FoxRider123',
                'roles' => ['ROLE_MEMBER']
            ],
            [
                'username' => 'ShredMaster',
                'email' => 'shredmaster@tricks.com',
                'password' => 'Shred2025',
                'roles' => ['ROLE_MEMBER']
            ],
            [
                'username' => 'MountainSoul',
                'email' => 'mountainsoul@tricks.com',
                'password' => 'SoulRide99',
                'roles' => ['ROLE_MEMBER']
            ],
        ];

        foreach ($usersData as $data) {
            $user = new Users();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));
            $user->setRoles($data['roles']);
            $user->setIsVerified(true);

            // --- slug automatique (optionnel ici car LifecycleCallback le gère)
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $data['username'])));
            $user->setSlug($slug);

            $manager->persist($user);

            // Créer une référence pour pouvoir l'utiliser dans d'autres fixtures
            $this->setReference($data['username'], $user);
        }

        $manager->flush();
    }
}
