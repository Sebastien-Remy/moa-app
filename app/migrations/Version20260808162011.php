<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808162011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create documents and document-tag associations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE document (
                id UUID NOT NULL,
                issued_at DATE NOT NULL,
                direction VARCHAR(255) NOT NULL,
                recorded_at DATE NOT NULL,
                valid_from DATE DEFAULT NULL,
                valid_until DATE DEFAULT NULL,
                reference VARCHAR(255) DEFAULT NULL,
                total_amount BIGINT DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                folder_id UUID DEFAULT NULL,
                document_type_id UUID DEFAULT NULL,
                status_id UUID DEFAULT NULL,
                third_party_id UUID DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            'CREATE INDEX IDX_D8698A76162CB942
             ON document (folder_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_D8698A7661232A4F
             ON document (document_type_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_D8698A766BF700BD
             ON document (status_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_D8698A7654C4149C
             ON document (third_party_id)'
        );

        $this->addSql(
            'CREATE TABLE document_tag (
                document_id UUID NOT NULL,
                tag_id UUID NOT NULL,
                PRIMARY KEY (document_id, tag_id)
            )'
        );

        $this->addSql(
            'CREATE INDEX IDX_D0234567C33F7837
             ON document_tag (document_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_D0234567BAD26311
             ON document_tag (tag_id)'
        );

        $this->addSql(
            "ALTER TABLE document
             ADD CONSTRAINT chk_document_direction
             CHECK (
                 direction IN ('incoming', 'outgoing', 'internal')
             )"
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT chk_document_total_amount_non_negative
             CHECK (
                 total_amount IS NULL
                 OR total_amount >= 0
             )'
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT chk_document_validity_period
             CHECK (
                 valid_from IS NULL
                 OR valid_until IS NULL
                 OR valid_until >= valid_from
             )'
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT FK_D8698A76162CB942
             FOREIGN KEY (folder_id)
             REFERENCES folder (id)
             ON DELETE SET NULL
             NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT FK_D8698A7661232A4F
             FOREIGN KEY (document_type_id)
             REFERENCES document_type (id)
             ON DELETE SET NULL
             NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT FK_D8698A766BF700BD
             FOREIGN KEY (status_id)
             REFERENCES status (id)
             ON DELETE SET NULL
             NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE document
             ADD CONSTRAINT FK_D8698A7654C4149C
             FOREIGN KEY (third_party_id)
             REFERENCES third_party (id)
             ON DELETE SET NULL
             NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE document_tag
             ADD CONSTRAINT FK_D0234567C33F7837
             FOREIGN KEY (document_id)
             REFERENCES document (id)
             ON DELETE CASCADE'
        );

        $this->addSql(
            'ALTER TABLE document_tag
             ADD CONSTRAINT FK_D0234567BAD26311
             FOREIGN KEY (tag_id)
             REFERENCES tag (id)
             ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE document_tag
             DROP CONSTRAINT FK_D0234567C33F7837'
        );

        $this->addSql(
            'ALTER TABLE document_tag
             DROP CONSTRAINT FK_D0234567BAD26311'
        );

        $this->addSql(
            'ALTER TABLE document
             DROP CONSTRAINT FK_D8698A76162CB942'
        );

        $this->addSql(
            'ALTER TABLE document
             DROP CONSTRAINT FK_D8698A7661232A4F'
        );

        $this->addSql(
            'ALTER TABLE document
             DROP CONSTRAINT FK_D8698A766BF700BD'
        );

        $this->addSql(
            'ALTER TABLE document
             DROP CONSTRAINT FK_D8698A7654C4149C'
        );

        $this->addSql('DROP TABLE document_tag');
        $this->addSql('DROP TABLE document');
    }
}
