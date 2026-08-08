<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808170907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create stored files and document attachments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE stored_file (
                id UUID NOT NULL,
                mime_type VARCHAR(255) NOT NULL,
                extension VARCHAR(20) DEFAULT NULL,
                size BIGINT NOT NULL,
                checksum VARCHAR(64) NOT NULL,
                imported_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_C339E77CDE6FDF9A
             ON stored_file (checksum)'
        );

        $this->addSql(
            "ALTER TABLE stored_file
             ADD CONSTRAINT chk_stored_file_mime_type_not_blank
             CHECK (BTRIM(mime_type) <> '')"
        );

        $this->addSql(
            'ALTER TABLE stored_file
             ADD CONSTRAINT chk_stored_file_size_positive
             CHECK (size > 0)'
        );

        $this->addSql(
            "ALTER TABLE stored_file
             ADD CONSTRAINT chk_stored_file_checksum_format
             CHECK (checksum ~ '^[a-f0-9]{64}$')"
        );

        $this->addSql(
            "ALTER TABLE stored_file
             ADD CONSTRAINT chk_stored_file_extension_format
             CHECK (
                 extension IS NULL
                 OR extension ~ '^[a-z0-9]+([._+-][a-z0-9]+)*$'
             )"
        );

        $this->addSql(
            'CREATE TABLE document_file (
                id UUID NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                document_id UUID NOT NULL,
                stored_file_id UUID NOT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            'CREATE INDEX IDX_2B2BBA83C33F7837
             ON document_file (document_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_2B2BBA837590B9E4
             ON document_file (stored_file_id)'
        );

        $this->addSql(
            "ALTER TABLE document_file
             ADD CONSTRAINT chk_document_file_original_name_not_blank
             CHECK (BTRIM(original_name) <> '')"
        );

        $this->addSql(
            'ALTER TABLE document_file
             ADD CONSTRAINT FK_2B2BBA83C33F7837
             FOREIGN KEY (document_id)
             REFERENCES document (id)
             ON DELETE CASCADE
             NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE document_file
             ADD CONSTRAINT FK_2B2BBA837590B9E4
             FOREIGN KEY (stored_file_id)
             REFERENCES stored_file (id)
             ON DELETE RESTRICT
             NOT DEFERRABLE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE document_file
             DROP CONSTRAINT FK_2B2BBA83C33F7837'
        );

        $this->addSql(
            'ALTER TABLE document_file
             DROP CONSTRAINT FK_2B2BBA837590B9E4'
        );

        $this->addSql('DROP TABLE document_file');
        $this->addSql('DROP TABLE stored_file');
    }
}
