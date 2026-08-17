<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add currency to Analysis and initialize existing analyses.
 */
final class Version20260817090608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add currency to Analysis and initialize existing analyses.';
    }

    public function up(Schema $schema): void
    {
        // Add the column as nullable first
        $this->addSql('ALTER TABLE analysis ADD currency_id UUID DEFAULT NULL');

        // Copy the currency from the related document
        $this->addSql('
            UPDATE analysis a
            SET currency_id = d.currency_id
            FROM document d
            WHERE a.document_id = d.id
              AND d.currency_id IS NOT NULL
              AND a.currency_id IS NULL
        ');

        // Copy the currency from the bank account of the related bank transaction
        $this->addSql('
            UPDATE analysis a
            SET currency_id = ba.currency_id
            FROM bank_transaction bt
            INNER JOIN bank_account ba ON ba.id = bt.bank_account_id
            WHERE a.bank_transaction_id = bt.id
              AND ba.currency_id IS NOT NULL
              AND a.currency_id IS NULL
        ');

        // Fallback to the default currency
        $this->addSql('
            UPDATE analysis a
            SET currency_id = c.id
            FROM currency c
            WHERE c.is_default = TRUE
              AND a.currency_id IS NULL
        ');

        // Make the column mandatory
        $this->addSql('ALTER TABLE analysis ALTER COLUMN currency_id SET NOT NULL');

        // Add the foreign key
        $this->addSql('
            ALTER TABLE analysis
            ADD CONSTRAINT FK_33C73038248176
            FOREIGN KEY (currency_id)
            REFERENCES currency (id)
            ON DELETE RESTRICT
            NOT DEFERRABLE
        ');

        // Add the index
        $this->addSql('CREATE INDEX IDX_33C73038248176 ON analysis (currency_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis DROP CONSTRAINT FK_33C73038248176');
        $this->addSql('DROP INDEX IDX_33C73038248176');
        $this->addSql('ALTER TABLE analysis DROP currency_id');
    }
}
