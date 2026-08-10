<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260810170357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change document.recordedAt from DATE to DATETIME to preserve the document registration time.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ALTER recorded_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ALTER recorded_at TYPE DATE');
    }
}
