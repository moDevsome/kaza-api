<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627082940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ContentTranslation and Location entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_translation (id INT AUTO_INCREMENT NOT NULL, translation_key VARCHAR(80) NOT NULL, translation_value VARCHAR(2500) NOT NULL, tag VARCHAR(6) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, area_id INT NOT NULL, INDEX IDX_5E9E89CBBD0F409C (area_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location_area (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBBD0F409C FOREIGN KEY (area_id) REFERENCES location_area (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBBD0F409C');
        $this->addSql('DROP TABLE content_translation');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE location_area');
    }
}
