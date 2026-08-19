<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260819090815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add XOR source constraint to analysis.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE analysis
         ADD CONSTRAINT chk_analysis_source
         CHECK (
             (
                 document_id IS NOT NULL
                 AND bank_transaction_id IS NULL
             )
             OR
             (
                 document_id IS NULL
                 AND bank_transaction_id IS NOT NULL
             )
         )'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE analysis
         DROP CONSTRAINT chk_analysis_source'
        );
    }
}
