<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260905080426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_translation (id INT AUTO_INCREMENT NOT NULL, translation_key VARCHAR(80) NOT NULL, translation_value VARCHAR(2500) NOT NULL, tag VARCHAR(6) NOT NULL, content_id BINARY(16) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment (id BINARY(16) NOT NULL, name VARCHAR(80) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_lodging (equipment_id BINARY(16) NOT NULL, lodging_id BINARY(16) NOT NULL, INDEX IDX_FB4ED7C5517FE9FE (equipment_id), INDEX IDX_FB4ED7C587335AF1 (lodging_id), PRIMARY KEY (equipment_id, lodging_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE host (id BINARY(16) NOT NULL, lastname VARCHAR(56) NOT NULL, firstname VARCHAR(56) NOT NULL, picture VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_CF2713FDA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, area_id BINARY(16) NOT NULL, INDEX IDX_5E9E89CBBD0F409C (area_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE location_area (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lodging (id BINARY(16) NOT NULL, title VARCHAR(255) NOT NULL, cover VARCHAR(255) DEFAULT NULL, description VARCHAR(800) NOT NULL, rating SMALLINT DEFAULT NULL, host_id BINARY(16) NOT NULL, location_id BINARY(16) NOT NULL, INDEX IDX_8D35182A1FB8D185 (host_id), INDEX IDX_8D35182A64D218E (location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lodging_tag (lodging_id BINARY(16) NOT NULL, tag_id BINARY(16) NOT NULL, INDEX IDX_A2602EC087335AF1 (lodging_id), INDEX IDX_A2602EC0BAD26311 (tag_id), PRIMARY KEY (lodging_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, lodging_id BINARY(16) NOT NULL, INDEX IDX_16DB4F8987335AF1 (lodging_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id BINARY(16) NOT NULL, name VARCHAR(56) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C5517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C587335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE host ADD CONSTRAINT FK_CF2713FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBBD0F409C FOREIGN KEY (area_id) REFERENCES location_area (id)');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A1FB8D185 FOREIGN KEY (host_id) REFERENCES host (id)');
        $this->addSql('ALTER TABLE lodging ADD CONSTRAINT FK_8D35182A64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC087335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_tag ADD CONSTRAINT FK_A2602EC0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8987335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C5517FE9FE');
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C587335AF1');
        $this->addSql('ALTER TABLE host DROP FOREIGN KEY FK_CF2713FDA76ED395');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBBD0F409C');
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A1FB8D185');
        $this->addSql('ALTER TABLE lodging DROP FOREIGN KEY FK_8D35182A64D218E');
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC087335AF1');
        $this->addSql('ALTER TABLE lodging_tag DROP FOREIGN KEY FK_A2602EC0BAD26311');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8987335AF1');
        $this->addSql('DROP TABLE content_translation');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE equipment_lodging');
        $this->addSql('DROP TABLE host');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE location_area');
        $this->addSql('DROP TABLE lodging');
        $this->addSql('DROP TABLE lodging_tag');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
    }
}
