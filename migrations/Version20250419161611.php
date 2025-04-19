<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250419161611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables product et category';
    }

    public function up(Schema $schema): void
    {
        // Création de la table category
        $this->addSql('CREATE TABLE category (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');

        // Création de la table product
        $this->addSql('CREATE TABLE product (
            id SERIAL NOT NULL,
            name VARCHAR(255) NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            is_featured BOOLEAN NOT NULL,
            category_id INT DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        // Clé étrangère vers category
        $this->addSql('ALTER TABLE product 
            ADD CONSTRAINT FK_product_category 
            FOREIGN KEY (category_id) REFERENCES category (id) 
            NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Suppression des tables dans l'ordre inverse
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_product_category');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE category');
    }
}
