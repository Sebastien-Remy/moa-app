<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260825081658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the unique default status index with a regular index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_status_default');
        $this->addSql('CREATE INDEX idx_status_is_default ON status (is_default)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_status_is_default');
        $this->addSql(
            'CREATE UNIQUE INDEX uniq_status_default ON status (is_default) WHERE (is_default = true)'
        );
    }
}
