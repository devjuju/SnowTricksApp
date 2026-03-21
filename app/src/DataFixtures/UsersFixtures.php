<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'username' => 'Jimmy',
                'email' => 'jimmy@example.com',
                'password' => 'motdepasse',
                'roles' => ['ROLE_MEMBER']
            ],
            [
                'username' => 'Anonyme',
                'email' => 'anonyme@example.com',
                'password' => 'passesecret',
                'roles' => ['ROLE_MEMBER']
            ],
            [
                'username' => 'Incognito',
                'email' => 'incognito@example.com',
                'password' => 'passecache',
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
