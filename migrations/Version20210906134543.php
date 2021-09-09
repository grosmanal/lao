<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210906134543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_7BF611AA6B899279');
        $this->addSql('DROP INDEX IDX_7BF611AA56352737');
        $this->addSql('DROP INDEX IDX_7BF611AAEDAE188E');
        $this->addSql('DROP INDEX IDX_7BF611AA3B84A2A7');
        $this->addSql('CREATE TEMPORARY TABLE __temp__care_request AS SELECT id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accepted_date, abandon_reason, abandon_date FROM care_request');
        $this->addSql('DROP TABLE care_request');
        $this->addSql('CREATE TABLE care_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, doctor_creator_id INTEGER DEFAULT NULL, complaint_id INTEGER DEFAULT NULL, accepted_by_doctor_id INTEGER DEFAULT NULL, creation_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , priority BOOLEAN NOT NULL, custom_complaint VARCHAR(255) DEFAULT NULL COLLATE BINARY, abandon_reason VARCHAR(10) DEFAULT NULL COLLATE BINARY, abandon_date DATE DEFAULT NULL --(DC2Type:date_immutable)
        , accept_date DATE DEFAULT NULL --(DC2Type:date_immutable)
        , CONSTRAINT FK_7BF611AA6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7BF611AA56352737 FOREIGN KEY (doctor_creator_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7BF611AAEDAE188E FOREIGN KEY (complaint_id) REFERENCES complaint (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7BF611AA3B84A2A7 FOREIGN KEY (accepted_by_doctor_id) REFERENCES doctor (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO care_request (id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accept_date, abandon_reason, abandon_date) SELECT id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accepted_date, abandon_reason, abandon_date FROM __temp__care_request');
        $this->addSql('DROP TABLE __temp__care_request');
        $this->addSql('CREATE INDEX IDX_7BF611AA6B899279 ON care_request (patient_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA56352737 ON care_request (doctor_creator_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AAEDAE188E ON care_request (complaint_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA3B84A2A7 ON care_request (accepted_by_doctor_id)');
        $this->addSql('DROP INDEX UNIQ_1FC0F36AA76ED395');
        $this->addSql('DROP INDEX IDX_1FC0F36AFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor AS SELECT id, user_id, office_id, first_name, last_name FROM doctor');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL COLLATE BINARY, last_name VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_1FC0F36AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1FC0F36AFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO doctor (id, user_id, office_id, first_name, last_name) SELECT id, user_id, office_id, first_name, last_name FROM __temp__doctor');
        $this->addSql('DROP TABLE __temp__doctor');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AA76ED395 ON doctor (user_id)');
        $this->addSql('CREATE INDEX IDX_1FC0F36AFFA0C224 ON doctor (office_id)');
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
        $this->addSql('DROP INDEX IDX_7BF611AA6B899279');
        $this->addSql('DROP INDEX IDX_7BF611AA56352737');
        $this->addSql('DROP INDEX IDX_7BF611AAEDAE188E');
        $this->addSql('DROP INDEX IDX_7BF611AA3B84A2A7');
        $this->addSql('CREATE TEMPORARY TABLE __temp__care_request AS SELECT id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accept_date, abandon_date, abandon_reason FROM care_request');
        $this->addSql('DROP TABLE care_request');
        $this->addSql('CREATE TABLE care_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, patient_id INTEGER NOT NULL, doctor_creator_id INTEGER DEFAULT NULL, complaint_id INTEGER DEFAULT NULL, accepted_by_doctor_id INTEGER DEFAULT NULL, creation_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , priority BOOLEAN NOT NULL, custom_complaint VARCHAR(255) DEFAULT NULL, abandon_date DATE DEFAULT NULL --(DC2Type:date_immutable)
        , abandon_reason VARCHAR(10) DEFAULT NULL, accepted_date DATE DEFAULT NULL --(DC2Type:date_immutable)
        )');
        $this->addSql('INSERT INTO care_request (id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accepted_date, abandon_date, abandon_reason) SELECT id, patient_id, doctor_creator_id, complaint_id, accepted_by_doctor_id, creation_date, priority, custom_complaint, accept_date, abandon_date, abandon_reason FROM __temp__care_request');
        $this->addSql('DROP TABLE __temp__care_request');
        $this->addSql('CREATE INDEX IDX_7BF611AA6B899279 ON care_request (patient_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA56352737 ON care_request (doctor_creator_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AAEDAE188E ON care_request (complaint_id)');
        $this->addSql('CREATE INDEX IDX_7BF611AA3B84A2A7 ON care_request (accepted_by_doctor_id)');
        $this->addSql('DROP INDEX UNIQ_1FC0F36AA76ED395');
        $this->addSql('DROP INDEX IDX_1FC0F36AFFA0C224');
        $this->addSql('CREATE TEMPORARY TABLE __temp__doctor AS SELECT id, user_id, office_id, first_name, last_name FROM doctor');
        $this->addSql('DROP TABLE doctor');
        $this->addSql('CREATE TABLE doctor (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, office_id INTEGER NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO doctor (id, user_id, office_id, first_name, last_name) SELECT id, user_id, office_id, first_name, last_name FROM __temp__doctor');
        $this->addSql('DROP TABLE __temp__doctor');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FC0F36AA76ED395 ON doctor (user_id)');
        $this->addSql('CREATE INDEX IDX_1FC0F36AFFA0C224 ON doctor (office_id)');
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
