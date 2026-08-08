<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260808150624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the folder table with case-insensitive name uniqueness';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE folder (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                color VARCHAR(7) DEFAULT NULL,
                fa_icon VARCHAR(100) DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            "ALTER TABLE folder
             ADD CONSTRAINT chk_folder_name_not_blank
             CHECK (BTRIM(name) <> '')"
        );

        $this->addSql(
            "ALTER TABLE folder
             ADD CONSTRAINT chk_folder_color_format
             CHECK (
                 color IS NULL
                 OR color ~ '^#[0-9A-Fa-f]{6}$'
             )"
        );

        $this->addSql(
            'CREATE UNIQUE INDEX uniq_folder_name_case_insensitive
             ON folder (LOWER(BTRIM(name)))'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE folder');
    }
}
