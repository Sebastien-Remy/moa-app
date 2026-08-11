<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260811123517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.2.1 - Create banking and reconciliation entities';
    }
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bank_account (id UUID NOT NULL, name VARCHAR(100) NOT NULL, bank_name VARCHAR(100) NOT NULL, iban VARCHAR(34) DEFAULT NULL, active BOOLEAN NOT NULL, currency_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_53A23E0A38248176 ON bank_account (currency_id)');
        $this->addSql('CREATE TABLE bank_transaction (id UUID NOT NULL, date DATE NOT NULL, value_date DATE DEFAULT NULL, bank_label VARCHAR(255) NOT NULL, notes TEXT DEFAULT NULL, amount BIGINT NOT NULL, reference VARCHAR(255) DEFAULT NULL, import_reference VARCHAR(255) DEFAULT NULL, bank_account_id UUID NOT NULL, third_party_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_50BCB3AE12CB990C ON bank_transaction (bank_account_id)');
        $this->addSql('CREATE INDEX IDX_50BCB3AE54C4149C ON bank_transaction (third_party_id)');
        $this->addSql('CREATE TABLE document_transaction (id UUID NOT NULL, amount BIGINT NOT NULL, document_id UUID NOT NULL, bank_transaction_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F09E32BBC33F7837 ON document_transaction (document_id)');
        $this->addSql('CREATE INDEX IDX_F09E32BBB898B7D6 ON document_transaction (bank_transaction_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_document_bank_transaction ON document_transaction (document_id, bank_transaction_id)');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A38248176 FOREIGN KEY (currency_id) REFERENCES currency (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE bank_transaction ADD CONSTRAINT FK_50BCB3AE12CB990C FOREIGN KEY (bank_account_id) REFERENCES bank_account (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE bank_transaction ADD CONSTRAINT FK_50BCB3AE54C4149C FOREIGN KEY (third_party_id) REFERENCES third_party (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE document_transaction ADD CONSTRAINT FK_F09E32BBC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE document_transaction ADD CONSTRAINT FK_F09E32BBB898B7D6 FOREIGN KEY (bank_transaction_id) REFERENCES bank_transaction (id) ON DELETE RESTRICT NOT DEFERRABLE');

        $this->addSql('CREATE INDEX IDX_BANK_ACCOUNT_ACTIVE ON bank_account (active)');

        $this->addSql('CREATE INDEX IDX_BANK_TRANSACTION_DATE ON bank_transaction (date)');
        $this->addSql('CREATE INDEX IDX_BANK_TRANSACTION_VALUE_DATE ON bank_transaction (value_date)');
        $this->addSql('CREATE INDEX IDX_BANK_TRANSACTION_REFERENCE ON bank_transaction (reference)');
        $this->addSql('CREATE INDEX IDX_BANK_TRANSACTION_IMPORT_REFERENCE ON bank_transaction (import_reference)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bank_account DROP CONSTRAINT FK_53A23E0A38248176');
        $this->addSql('ALTER TABLE bank_transaction DROP CONSTRAINT FK_50BCB3AE12CB990C');
        $this->addSql('ALTER TABLE bank_transaction DROP CONSTRAINT FK_50BCB3AE54C4149C');
        $this->addSql('ALTER TABLE document_transaction DROP CONSTRAINT FK_F09E32BBC33F7837');
        $this->addSql('ALTER TABLE document_transaction DROP CONSTRAINT FK_F09E32BBB898B7D6');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE bank_transaction');
        $this->addSql('DROP TABLE document_transaction');
    }
}
