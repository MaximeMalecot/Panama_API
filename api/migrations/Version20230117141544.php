<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117141544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE freelancer_info_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE freelancer_info (id INT NOT NULL, freelancer_id INT NOT NULL, is_verified BOOLEAN NOT NULL, description TEXT DEFAULT NULL, phone_nb VARCHAR(12) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7EFF784D8545BDF5 ON freelancer_info (freelancer_id)');
        $this->addSql('ALTER TABLE freelancer_info ADD CONSTRAINT FK_7EFF784D8545BDF5 FOREIGN KEY (freelancer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE freelancer_info_id_seq CASCADE');
        $this->addSql('DROP TABLE freelancer_info');
    }
}
