<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260825080653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add default flag to status and ensure only one status can be default';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE status ADD is_default BOOLEAN DEFAULT false NOT NULL'
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_status_default
             ON status (is_default)
             WHERE is_default = true'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DROP INDEX uniq_status_default'
        );

        $this->addSql(
            'ALTER TABLE status DROP is_default'
        );
    }
}
