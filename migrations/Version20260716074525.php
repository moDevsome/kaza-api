<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716074525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C587335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC087335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8987335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C587335AF1');
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC087335AF1');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8987335AF1');
    }
}
