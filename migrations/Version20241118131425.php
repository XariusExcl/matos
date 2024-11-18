<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241118131425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unavailable_days ADD restricted_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unavailable_days ADD CONSTRAINT FK_3A528D7C1C4C452 FOREIGN KEY (restricted_category_id) REFERENCES equipment_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3A528D7C1C4C452 ON unavailable_days (restricted_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE unavailable_days DROP CONSTRAINT FK_3A528D7C1C4C452');
        $this->addSql('DROP INDEX IDX_3A528D7C1C4C452');
        $this->addSql('ALTER TABLE unavailable_days DROP restricted_category_id');
    }
}
