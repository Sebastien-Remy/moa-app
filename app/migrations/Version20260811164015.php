<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811164015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.4.1 - Create analysis';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE analysis (id UUID NOT NULL, amount BIGINT NOT NULL, notes TEXT DEFAULT NULL, document_id UUID DEFAULT NULL, bank_transaction_id UUID DEFAULT NULL, category_id UUID DEFAULT NULL, PRIMARY KEY (id))');

        $this->addSql('CREATE INDEX IDX_33C730C33F7837 ON analysis (document_id)');
        $this->addSql('CREATE INDEX IDX_33C730B898B7D6 ON analysis (bank_transaction_id)');
        $this->addSql('CREATE INDEX IDX_33C73012469DE2 ON analysis (category_id)');

        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C730C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C730B898B7D6 FOREIGN KEY (bank_transaction_id) REFERENCES bank_transaction (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE analysis ADD CONSTRAINT FK_33C73012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE RESTRICT NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis DROP CONSTRAINT FK_33C730C33F7837');
        $this->addSql('ALTER TABLE analysis DROP CONSTRAINT FK_33C730B898B7D6');
        $this->addSql('ALTER TABLE analysis DROP CONSTRAINT FK_33C73012469DE2');

        $this->addSql('DROP TABLE analysis');
    }
}
