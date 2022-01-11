<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111095605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_request ADD requested_doctor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA778A1B1A FOREIGN KEY (requested_doctor_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA778A1B1A ON care_request (requested_doctor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA778A1B1A');
        $this->addSql('DROP INDEX IDX_7BF611AA778A1B1A ON care_request');
        $this->addSql('ALTER TABLE care_request DROP requested_doctor_id');
    }
}
