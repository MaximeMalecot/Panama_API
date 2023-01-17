<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117144723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE proposition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE review_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE proposition (id INT NOT NULL, project_id INT DEFAULT NULL, client_id INT NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7CDC353166D1F9C ON proposition (project_id)');
        $this->addSql('CREATE INDEX IDX_C7CDC35319EB6921 ON proposition (client_id)');
        $this->addSql('CREATE TABLE review (id INT NOT NULL, mark DOUBLE PRECISION NOT NULL, content TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE proposition ADD CONSTRAINT FK_C7CDC353166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE proposition ADD CONSTRAINT FK_C7CDC35319EB6921 FOREIGN KEY (client_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE subscription ALTER is_active SET DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE proposition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE review_id_seq CASCADE');
        $this->addSql('DROP TABLE proposition');
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE subscription DROP created_at');
        $this->addSql('ALTER TABLE subscription DROP updated_at');
        $this->addSql('ALTER TABLE subscription ALTER is_active DROP DEFAULT');
    }
}
