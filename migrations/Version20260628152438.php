<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260628152438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor relation between "Lodging" and "Tag".';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lodging_tag (lodging_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_A2602EC087335AF1 (lodging_id), INDEX IDX_A2602EC0BAD26311 (tag_id), PRIMARY KEY (lodging_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC087335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY `FK_8D35182A8D7B4FB4`');
        $this->addSql('DROP INDEX IDX_8D35182A8D7B4FB4 ON lodging');
        $this->addSql('ALTER TABLE lodging DROP tags_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC087335AF1');
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC0BAD26311');
        $this->addSql('DROP TABLE lodging_tag');
        $this->addSql('ALTER TABLE lodging ADD tags_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT `FK_8D35182A8D7B4FB4` FOREIGN KEY (tags_id) REFERENCES tag (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D35182A8D7B4FB4 ON lodging (tags_id)');
    }
}
