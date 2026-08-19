<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260819085650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create third party entries for receivable and payable positions.';
    }
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE third_party_entry (id UUID NOT NULL, entry_date DATE NOT NULL, amount BIGINT NOT NULL, notes TEXT DEFAULT NULL, third_party_id UUID NOT NULL, document_id UUID DEFAULT NULL, bank_transaction_id UUID DEFAULT NULL, currency_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_748A562B54C4149C ON third_party_entry (third_party_id)');
        $this->addSql('CREATE INDEX IDX_748A562BC33F7837 ON third_party_entry (document_id)');
        $this->addSql('CREATE INDEX IDX_748A562BB898B7D6 ON third_party_entry (bank_transaction_id)');
        $this->addSql('CREATE INDEX IDX_748A562B38248176 ON third_party_entry (currency_id)');
        $this->addSql('ALTER TABLE third_party_entry ADD CONSTRAINT FK_748A562B54C4149C FOREIGN KEY (third_party_id) REFERENCES third_party (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE third_party_entry ADD CONSTRAINT FK_748A562BC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE third_party_entry ADD CONSTRAINT FK_748A562BB898B7D6 FOREIGN KEY (bank_transaction_id) REFERENCES bank_transaction (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE third_party_entry ADD CONSTRAINT FK_748A562B38248176 FOREIGN KEY (currency_id) REFERENCES currency (id) ON DELETE RESTRICT NOT DEFERRABLE');

        $this->addSql(
            'ALTER TABLE third_party_entry
             ADD CONSTRAINT chk_third_party_entry_source
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
        $this->addSql('ALTER TABLE third_party_entry DROP CONSTRAINT FK_748A562B54C4149C');
        $this->addSql('ALTER TABLE third_party_entry DROP CONSTRAINT FK_748A562BC33F7837');
        $this->addSql('ALTER TABLE third_party_entry DROP CONSTRAINT FK_748A562BB898B7D6');
        $this->addSql('ALTER TABLE third_party_entry DROP CONSTRAINT FK_748A562B38248176');
        $this->addSql('DROP TABLE third_party_entry');
    }
}
