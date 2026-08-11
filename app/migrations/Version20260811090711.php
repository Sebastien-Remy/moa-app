<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20260811090711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.1.4 - Add currency to documents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ADD currency_id UUID DEFAULT NULL');

        $this->abortIf(
            (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM currency WHERE is_default = TRUE'
            ) === 0,
            'A default currency must exist before running this migration.',
        );

        $this->addSql("
            UPDATE document
            SET currency_id = (
                SELECT id
                FROM currency
                WHERE is_default = TRUE
                LIMIT 1
            )
            WHERE total_amount IS NOT NULL
        ");

        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7638248176 FOREIGN KEY (currency_id) REFERENCES currency (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D8698A7638248176 ON document (currency_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A7638248176');
        $this->addSql('DROP INDEX IDX_D8698A7638248176');
        $this->addSql('ALTER TABLE document DROP currency_id');
    }
}
