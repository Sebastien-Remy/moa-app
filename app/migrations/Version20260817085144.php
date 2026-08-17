<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260817085144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add analysis date to support financial allocations across accounting periods.';
    }

    public function up(Schema $schema): void
    {
        // Add the new column as nullable
        $this->addSql('ALTER TABLE analysis ADD analysis_date DATE DEFAULT NULL');

        // Initialize document analyses from the document issue date
        $this->addSql('
        UPDATE analysis a
        SET analysis_date = d.issued_at
        FROM document d
        WHERE a.document_id = d.id
          AND d.issued_at IS NOT NULL
          AND a.analysis_date IS NULL
    ');

        // Initialize bank transaction analyses from the transaction date
        $this->addSql('
        UPDATE analysis a
        SET analysis_date = bt.date
        FROM bank_transaction bt
        WHERE a.bank_transaction_id = bt.id
          AND a.analysis_date IS NULL
    ');

        // Existing document analyses without an issue date still need a date.
        // Use the document recording date as the migration fallback.
        $this->addSql('
        UPDATE analysis a
        SET analysis_date = d.recorded_at::date
        FROM document d
        WHERE a.document_id = d.id
          AND a.analysis_date IS NULL
    ');

        // Make the column mandatory
        $this->addSql('ALTER TABLE analysis ALTER COLUMN analysis_date SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analysis DROP analysis_date');
    }
}
