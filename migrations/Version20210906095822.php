<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210906095822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_B67687BEFFA0C224');
        $this->addSql('DROP INDEX UNIQ_B67687BEA76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctors AS SELECT id, user_id, office_id, first_name, last_name FROM doctors');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('CREATE TABLE doctors (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL COLLATE BINARY, last_name VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_B67687BEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B67687BEFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO doctors (id, user_id, office_id, first_name, last_name) SELECT id, user_id, office_id, first_name, last_name FROM __temp__doctors');
        $this->addSql('DROP TABLE __temp__doctors');
        $this->addSql('CREATE INDEX IDX_B67687BEFFA0C224 ON doctors (office_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B67687BEA76ED395 ON doctors (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, office_id INTEGER NOT NULL, firstname VARCHAR(255) DEFAULT NULL COLLATE BINARY, lastname VARCHAR(255) DEFAULT NULL COLLATE BINARY, birtdate DATE DEFAULT NULL --(DC2Type:date_immutable)
        , contact VARCHAR(255) DEFAULT NULL COLLATE BINARY, phone VARCHAR(255) DEFAULT NULL COLLATE BINARY, mobile_phone VARCHAR(255) DEFAULT NULL COLLATE BINARY, email VARCHAR(255) DEFAULT NULL COLLATE BINARY, variable_schedule BOOLEAN NOT NULL, availability CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , CONSTRAINT FK_1ADAD7EBFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO patient (id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability) SELECT id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBFFA0C224 ON patient (office_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_B67687BEA76ED395');
        $this->addSql('DROP INDEX IDX_B67687BEFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctors AS SELECT id, user_id, office_id, first_name, last_name FROM doctors');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('CREATE TABLE doctors (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO doctors (id, user_id, office_id, first_name, last_name) SELECT id, user_id, office_id, first_name, last_name FROM __temp__doctors');
        $this->addSql('DROP TABLE __temp__doctors');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B67687BEA76ED395 ON doctors (user_id)');
        $this->addSql('CREATE INDEX IDX_B67687BEFFA0C224 ON doctors (office_id)');
        $this->addSql('DROP INDEX IDX_1ADAD7EBFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, birtdate DATE DEFAULT NULL --(DC2Type:date_immutable)
        , contact VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, mobile_phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, variable_schedule BOOLEAN NOT NULL, availability CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO patient (id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability) SELECT id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
    }
}
