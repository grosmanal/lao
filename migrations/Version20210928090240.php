<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210928090240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA56352737 FOREIGN KEY (doctor_creator_id) REFERENCES doctor (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AAEDAE188E FOREIGN KEY (complaint_id) REFERENCES complaint (id)');
        $this->addSql('ALTER TABLE care_request ADD CONSTRAINT FK_7BF611AA3B84A2A7 FOREIGN KEY (accepted_by_doctor_id) REFERENCES doctor (id)');
        $this->addSql('ALTER TABLE doctor ADD CONSTRAINT FK_1FC0F36AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE doctor ADD CONSTRAINT FK_1FC0F36AFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBFFA0C224 FOREIGN KEY (office_id) REFERENCES office (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA6B899279');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA56352737');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AAEDAE188E');
        $this->addSql('ALTER TABLE care_request DROP FOREIGN KEY FK_7BF611AA3B84A2A7');
        $this->addSql('ALTER TABLE doctor DROP FOREIGN KEY FK_1FC0F36AA76ED395');
        $this->addSql('ALTER TABLE doctor DROP FOREIGN KEY FK_1FC0F36AFFA0C224');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBFFA0C224');
    }
}
