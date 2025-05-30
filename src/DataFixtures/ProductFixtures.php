<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Review;
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
                    ->setImage("https://picsum.photos/seed/".md5($product->getName())."/640/480")
                    ->setFeatured($faker->boolean(30));
            
            $manager->persist($product);

            for ($j = 0; $j < 10; $j++) {
                $review = new Review();
                $review->setUsername($faker->firstName);
                $review->setComment($faker->paragraph(2));
                $review->setRating($faker->numberBetween(3, 5)); // Note entre 3 et 5
                $review->setCreatedAt(
                    \DateTimeImmutable::createFromMutable(
                        $faker->dateTimeBetween('-3 months')
                    )
                );
    
                $review->setProduct($product); 
                $manager->persist($review);
            }    
        }

        $manager->flush();
    }
}