<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210906125536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE care_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, doctor_creator_id INTEGER DEFAULT NULL, complaint_id INTEGER DEFAULT NULL, accepted_by_doctor_id INTEGER DEFAULT NULL, state VARCHAR(10) NOT NULL, creation_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , priority BOOLEAN NOT NULL, custom_complaint VARCHAR(255) DEFAULT NULL, accepted_date DATE DEFAULT NULL --(DC2Type:date_immutable)
        , abandon_reason VARCHAR(10) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_7BF611AA6B899279 ON care_request (patient_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA56352737 ON care_request (doctor_creator_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AAEDAE188E ON care_request (complaint_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA3B84A2A7 ON care_request (accepted_by_doctor_id)');
        $this->addSql('CREATE TABLE complaint (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AA76ED395 ON doctor (user_id)');
        $this->addSql('CREATE INDEX IDX_1FC0F36AFFA0C224 ON doctor (office_id)');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('DROP INDEX IDX_1ADAD7EBFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, office_id INTEGER NOT NULL, firstname VARCHAR(255) DEFAULT NULL COLLATE BINARY, lastname VARCHAR(255) DEFAULT NULL COLLATE BINARY, birtdate DATE DEFAULT NULL --(DC2Type:date_immutable)
        , contact VARCHAR(255) DEFAULT NULL COLLATE BINARY, phone VARCHAR(255) DEFAULT NULL COLLATE BINARY, mobile_phone VARCHAR(255) DEFAULT NULL COLLATE BINARY, email VARCHAR(255) DEFAULT NULL COLLATE BINARY, variable_schedule BOOLEAN NOT NULL, availability CLOB NOT NULL COLLATE BINARY --(DC2Type:json)
        , CONSTRAINT FK_1ADAD7EBFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO patient (id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability) SELECT id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBFFA0C224 ON patient (office_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doctors (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL COLLATE BINARY, last_name VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B67687BEA76ED395 ON doctors (user_id)');
        $this->addSql('CREATE INDEX IDX_B67687BEFFA0C224 ON doctors (office_id)');
        $this->addSql('DROP TABLE care_request');
        $this->addSql('DROP TABLE complaint');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('DROP INDEX IDX_1ADAD7EBFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__patient AS SELECT id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM patient');
        $this->addSql('DROP TABLE patient');
        $this->addSql('CREATE TABLE patient (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, office_id INTEGER NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, birtdate DATE DEFAULT NULL --(DC2Type:date_immutable)
        , contact VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, mobile_phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, variable_schedule BOOLEAN NOT NULL, availability CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO patient (id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability) SELECT id, office_id, firstname, lastname, birtdate, contact, phone, mobile_phone, email, variable_schedule, availability FROM __temp__patient');
        $this->addSql('DROP TABLE __temp__patient');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBFFA0C224 ON patient (office_id)');
    }
}
