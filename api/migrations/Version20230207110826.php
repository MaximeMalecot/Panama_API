<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230207110826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C68545BDF5');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68545BDF5 FOREIGN KEY (freelancer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT fk_794381c68545bdf5');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT fk_794381c68545bdf5 FOREIGN KEY (freelancer_id) REFERENCES freelancer_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
