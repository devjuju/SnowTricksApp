<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

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
