<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210829163627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, birtdate DATE DEFAULT NULL --(DC2Type:date_immutable)
        , contact VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, mobile_phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, variable_schedule BOOLEAN NOT NULL, availability CLOB NOT NULL --(DC2Type:json)
        )');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE patient');
    }
}
