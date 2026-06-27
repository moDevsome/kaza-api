<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627074133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Init database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(56) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE host (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(56) NOT NULL, firstname VARCHAR(56) NOT NULL, picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lodging (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, cover VARCHAR(255) DEFAULT NULL, description VARCHAR(800) NOT NULL, rating SMALLINT NOT NULL, location VARCHAR(45) NOT NULL, host_id_id INT NOT NULL, tags_id INT DEFAULT NULL, INDEX IDX_8D35182ADC26D3A4 (host_id_id), INDEX IDX_8D35182A8D7B4FB4 (tags_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lodging_equipments (lodging_id INT NOT NULL, equipments_id INT NOT NULL, INDEX IDX_F0F5D6E587335AF1 (lodging_id), INDEX IDX_F0F5D6E5BD251DD7 (equipments_id), PRIMARY KEY (lodging_id, equipments_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, lodging_id_id INT NOT NULL, INDEX IDX_16DB4F892DC30898 (lodging_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(56) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182ADC26D3A4 FOREIGN KEY (host_id_id) REFERENCES host (id)');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A8D7B4FB4 FOREIGN KEY (tags_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE lodging_equipments ADD CONSTRAINT FK_F0F5D6E587335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_equipments ADD CONSTRAINT FK_F0F5D6E5BD251DD7 FOREIGN KEY (equipments_id) REFERENCES equipments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F892DC30898 FOREIGN KEY (lodging_id_id) REFERENCES lodging (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182ADC26D3A4');
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A8D7B4FB4');
        $this->addSql('ALTER TABLE lodging_equipments DROP FOREIGN KEY FK_F0F5D6E587335AF1');
        $this->addSql('ALTER TABLE lodging_equipments DROP FOREIGN KEY FK_F0F5D6E5BD251DD7');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F892DC30898');
        $this->addSql('DROP TABLE equipments');
        $this->addSql('DROP TABLE host');
        $this->addSql('DROP TABLE lodging');
        $this->addSql('DROP TABLE lodging_equipments');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE tag');
    }
}
