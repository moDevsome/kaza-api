<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260628085745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove location string field then replace it a Location entity relation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging ADD location_id INT NOT NULL, DROP location');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A918DB72 FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_8D35182A918DB72 ON lodging (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A918DB72');
        $this->addSql('DROP INDEX IDX_8D35182A918DB72 ON lodging');
        $this->addSql('ALTER TABLE lodging ADD location VARCHAR(45) NOT NULL, DROP location_id');
    }
}
