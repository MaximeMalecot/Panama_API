<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117142641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscription_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE filter (id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE filter_project (filter_id INT NOT NULL, project_id INT NOT NULL, PRIMARY KEY(filter_id, project_id))');
        $this->addSql('CREATE INDEX IDX_9823A78BD395B25E ON filter_project (filter_id)');
        $this->addSql('CREATE INDEX IDX_9823A78B166D1F9C ON filter_project (project_id)');
        $this->addSql('CREATE TABLE subscription_plan (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, color VARCHAR(7) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE filter_project ADD CONSTRAINT FK_9823A78BD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_project ADD CONSTRAINT FK_9823A78B166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE filter_project DROP CONSTRAINT FK_9823A78BD395B25E');
        $this->addSql('DROP SEQUENCE filter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscription_plan_id_seq CASCADE');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE filter_project');
        $this->addSql('DROP TABLE subscription_plan');
    }
}
