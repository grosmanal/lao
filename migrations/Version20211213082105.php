<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213082105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abandon_reason (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE care_request (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, doctor_creator_id INT DEFAULT NULL, modifier_id INT DEFAULT NULL, complaint_id INT DEFAULT NULL, accepted_by_doctor_id INT DEFAULT NULL, abandon_reason_id INT DEFAULT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modification_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', priority TINYINT(1) DEFAULT NULL, custom_complaint VARCHAR(255) DEFAULT NULL, accept_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', abandon_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_7BF611AA6B899279 (patient_id), INDEX IDX_7BF611AA56352737 (doctor_creator_id), INDEX IDX_7BF611AAD079F553 (modifier_id), INDEX IDX_7BF611AAEDAE188E (complaint_id), INDEX IDX_7BF611AA3B84A2A7 (accepted_by_doctor_id), INDEX IDX_7BF611AA5E4DA3C0 (abandon_reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, care_request_id INT NOT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modification_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', content LONGTEXT DEFAULT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526CB5A969C4 (care_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE complaint (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, comment_id INT NOT NULL, doctor_id INT NOT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', read_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAF8697D13 (comment_id), INDEX IDX_BF5476CA87F4FB17 (doctor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE office (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, address_complement1 VARCHAR(255) DEFAULT NULL, address_complement2 VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, modifier_id INT DEFAULT NULL, office_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) DEFAULT NULL, birthdate DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', contact VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, mobile_phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, variable_schedule TINYINT(1) DEFAULT NULL, creation_date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modification_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', availability LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_1ADAD7EB61220EA6 (creator_id), INDEX IDX_1ADAD7EBD079F553 (modifier_id), INDEX IDX_1ADAD7EBFFA0C224 (office_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, office_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', avatar_name VARCHAR(255) DEFAULT NULL, profil VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649FFA0C224 (office_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA56352737 FOREIGN KEY (doctor_creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AAD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AAEDAE188E FOREIGN KEY (complaint_id) REFERENCES complaint (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA3B84A2A7 FOREIGN KEY (accepted_by_doctor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA5E4DA3C0 FOREIGN KEY (abandon_reason_id) REFERENCES abandon_reason (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CB5A969C4 FOREIGN KEY (care_request_id) REFERENCES care_request (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA87F4FB17 FOREIGN KEY (doctor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB61220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBD079F553 FOREIGN KEY (modifier_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649FFA0C224 FOREIGN KEY (office_id) REFERENCES office (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA5E4DA3C0');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CB5A969C4');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF8697D13');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AAEDAE188E');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBFFA0C224');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649FFA0C224');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA6B899279');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA56352737');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AAD079F553');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA3B84A2A7');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA87F4FB17');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB61220EA6');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBD079F553');
        $this->addSql('DROP TABLE abandon_reason');
        $this->addSql('DROP TABLE care_request');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE complaint');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE office');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE `user`');
    }
}
