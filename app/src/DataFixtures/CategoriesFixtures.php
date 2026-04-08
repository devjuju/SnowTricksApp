<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

// Jeux de données représentant les principales catégories de tricks snowboard
// utilisées pour structurer la base de contenu du site SnowTricks

class CategoriesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['Grabs', 'Spins', 'Flips', 'Rails'];

        foreach ($categories as $name) {
            $category = new Categories();
            $category->setName($name);
            $manager->persist($category);
            $this->addReference($name, $category);
        }

        $manager->flush();
    }
}
