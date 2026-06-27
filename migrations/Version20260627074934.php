<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627074934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix "picture" and "host" foreign key name.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY `FK_8D35182ADC26D3A4`');
        $this->addSql('DROP INDEX IDX_8D35182ADC26D3A4 ON lodging');
        $this->addSql('ALTER TABLE lodging CHANGE host_id_id host_id INT NOT NULL');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A1FB8D185 FOREIGN KEY (host_id) REFERENCES host (id)');
        $this->addSql('CREATE INDEX IDX_8D35182A1FB8D185 ON lodging (host_id)');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY `FK_16DB4F892DC30898`');
        $this->addSql('DROP INDEX IDX_16DB4F892DC30898 ON picture');
        $this->addSql('ALTER TABLE picture CHANGE lodging_id_id lodging_id INT NOT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8987335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id)');
        $this->addSql('CREATE INDEX IDX_16DB4F8987335AF1 ON picture (lodging_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A1FB8D185');
        $this->addSql('DROP INDEX IDX_8D35182A1FB8D185 ON lodging');
        $this->addSql('ALTER TABLE lodging CHANGE host_id host_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT `FK_8D35182ADC26D3A4` FOREIGN KEY (host_id_id) REFERENCES host (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D35182ADC26D3A4 ON lodging (host_id_id)');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8987335AF1');
        $this->addSql('DROP INDEX IDX_16DB4F8987335AF1 ON picture');
        $this->addSql('ALTER TABLE picture CHANGE lodging_id lodging_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT `FK_16DB4F892DC30898` FOREIGN KEY (lodging_id_id) REFERENCES lodging (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_16DB4F892DC30898 ON picture (lodging_id_id)');
    }
}
