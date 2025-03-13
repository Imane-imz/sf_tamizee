<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Utilisation de Faker avec fakerphp/faker
        $faker = Factory::create();

        // Générer 50 produits fictifs
        for ($i = 0; $i < 50; $i++) {
            $product = new Product();
            $product->setName($faker->unique()->word)
                    ->setDescription($faker->text(200))
                    ->setPrice($faker->randomFloat(2, 10, 100))
                    ->setStock($faker->numberBetween(1, 100))
                    ->setImage("https://picsum.photos/seed/".md5($product->getName())."/640/480");

            $manager->persist($product);
        }

        $manager->flush();
    }
}