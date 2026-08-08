<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808153321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create document type, status, tag and third-party reference tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE document_type (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT NULL,
                fa_icon VARCHAR(100) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            "ALTER TABLE document_type
             ADD CONSTRAINT chk_document_type_name_not_blank
             CHECK (BTRIM(name) <> '')"
        );

        $this->addSql(
            "ALTER TABLE document_type
             ADD CONSTRAINT chk_document_type_color_format
             CHECK (
                 color IS NULL
                 OR color ~ '^#[0-9A-Fa-f]{6}$'
             )"
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_document_type_name_case_insensitive
             ON document_type (LOWER(BTRIM(name)))'
        );

        $this->addSql(
            'CREATE TABLE status (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT NULL,
                fa_icon VARCHAR(100) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            "ALTER TABLE status
             ADD CONSTRAINT chk_status_name_not_blank
             CHECK (BTRIM(name) <> '')"
        );

        $this->addSql(
            "ALTER TABLE status
             ADD CONSTRAINT chk_status_color_format
             CHECK (
                 color IS NULL
                 OR color ~ '^#[0-9A-Fa-f]{6}$'
             )"
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_status_name_case_insensitive
             ON status (LOWER(BTRIM(name)))'
        );

        $this->addSql(
            'CREATE TABLE tag (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT NULL,
                fa_icon VARCHAR(100) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            "ALTER TABLE tag
             ADD CONSTRAINT chk_tag_name_not_blank
             CHECK (BTRIM(name) <> '')"
        );

        $this->addSql(
            "ALTER TABLE tag
             ADD CONSTRAINT chk_tag_color_format
             CHECK (
                 color IS NULL
                 OR color ~ '^#[0-9A-Fa-f]{6}$'
             )"
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_tag_name_case_insensitive
             ON tag (LOWER(BTRIM(name)))'
        );

        $this->addSql(
            'CREATE TABLE third_party (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT NULL,
                fa_icon VARCHAR(100) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            "ALTER TABLE third_party
             ADD CONSTRAINT chk_third_party_name_not_blank
             CHECK (BTRIM(name) <> '')"
        );

        $this->addSql(
            "ALTER TABLE third_party
             ADD CONSTRAINT chk_third_party_color_format
             CHECK (
                 color IS NULL
                 OR color ~ '^#[0-9A-Fa-f]{6}$'
             )"
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_third_party_name_case_insensitive
             ON third_party (LOWER(BTRIM(name)))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE document_type');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE third_party');
    }
}
