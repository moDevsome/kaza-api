<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260628151128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace "Equipments" entity by "Equipment" and refactor the relation between "Lodging" and "Equipment".';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(80) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_lodging (equipment_id INT NOT NULL, lodging_id INT NOT NULL, INDEX IDX_FB4ED7C5517FE9FE (equipment_id), INDEX IDX_FB4ED7C587335AF1 (lodging_id), PRIMARY KEY (equipment_id, lodging_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C5517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment_lodging ADD CONSTRAINT FK_FB4ED7C587335AF1 FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_equipments DROP FOREIGN KEY `FK_F0F5D6E587335AF1`');
        $this->addSql('ALTER TABLE lodging_equipments DROP FOREIGN KEY `FK_F0F5D6E5BD251DD7`');
        $this->addSql('DROP TABLE equipments');
        $this->addSql('DROP TABLE lodging_equipments');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(56) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE lodging_equipments (lodging_id INT NOT NULL, equipments_id INT NOT NULL, INDEX IDX_F0F5D6E587335AF1 (lodging_id), INDEX IDX_F0F5D6E5BD251DD7 (equipments_id), PRIMARY KEY (lodging_id, equipments_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE lodging_equipments ADD CONSTRAINT `FK_F0F5D6E587335AF1` FOREIGN KEY (lodging_id) REFERENCES lodging (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lodging_equipments ADD CONSTRAINT `FK_F0F5D6E5BD251DD7` FOREIGN KEY (equipments_id) REFERENCES equipments (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C5517FE9FE');
        $this->addSql('ALTER TABLE equipment_lodging DROP FOREIGN KEY FK_FB4ED7C587335AF1');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE equipment_lodging');
    }
}
