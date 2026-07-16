<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716083540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C5517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A1FB8D185 FOREIGN KEY (host_id) REFERENCES host (id)');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C5517FE9FE');
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A1FB8D185');
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC0BAD26311');
    }
}
