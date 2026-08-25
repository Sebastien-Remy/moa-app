<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260825091933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add document model metadata and model lookup index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ADD is_model BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE document ADD model_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_document_is_model ON document (is_model)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_document_is_model');
        $this->addSql('ALTER TABLE document DROP is_model');
        $this->addSql('ALTER TABLE document DROP model_name');
    }
}
