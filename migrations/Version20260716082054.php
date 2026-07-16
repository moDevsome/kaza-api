<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716082054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE equipment_lodging CHANGE equipment_id equipment_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE host CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE lodging CHANGE host_id host_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE lodging_tag CHANGE tag_id tag_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE tag CHANGE id id BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE equipment_lodging CHANGE equipment_id equipment_id INT NOT NULL');
        $this->addSql('ALTER TABLE host CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE lodging CHANGE host_id host_id INT NOT NULL');
        $this->addSql('ALTER TABLE lodging_tag CHANGE tag_id tag_id INT NOT NULL');
        $this->addSql('ALTER TABLE tag CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
