<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127145210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ordered_products (id INT AUTO_INCREMENT NOT NULL, _order_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_39EA2925A35F2858 (_order_id), INDEX IDX_39EA29254584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ordered_products ADD CONSTRAINT FK_39EA2925A35F2858 FOREIGN KEY (_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE ordered_products ADD CONSTRAINT FK_39EA29254584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `order` ADD total_price DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ordered_products DROP FOREIGN KEY FK_39EA2925A35F2858');
        $this->addSql('ALTER TABLE ordered_products DROP FOREIGN KEY FK_39EA29254584665A');
        $this->addSql('DROP TABLE ordered_products');
        $this->addSql('ALTER TABLE `order` DROP total_price');
    }
}
