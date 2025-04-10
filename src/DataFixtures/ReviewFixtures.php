<?php

namespace App\DataFixtures;

use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReviewFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $review = new Review();
            $review->setUsername($faker->firstName);
            $review->setComment($faker->paragraph(2));
            $review->setRating($faker->numberBetween(3, 5)); // Note entre 3 et 5
            $review->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-3 months')
                )
            );

            $manager->persist($review);
        }

        $manager->flush();
    }
}
