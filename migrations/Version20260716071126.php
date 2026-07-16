<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716071126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging CHANGE lodging_id lodging_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE lodging CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE lodging_tag CHANGE lodging_id lodging_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE picture CHANGE lodging_id lodging_id BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging CHANGE lodging_id lodging_id VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE lodging CHANGE id id VARCHAR(32) DEFAULT \'5555-5555\' NOT NULL');
        $this->addSql('ALTER TABLE lodging_tag CHANGE lodging_id lodging_id VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE picture CHANGE lodging_id lodging_id VARCHAR(32) NOT NULL');
    }
}
