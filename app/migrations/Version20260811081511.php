<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

final class Version20260811081511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.1.1 - Create Currency reference table and seed the default EUR currency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE currency (
                id UUID NOT NULL,
                code VARCHAR(3) NOT NULL,
                name VARCHAR(100) NOT NULL,
                symbol VARCHAR(10) DEFAULT NULL,
                decimal_places SMALLINT NOT NULL,
                active BOOLEAN NOT NULL,
                is_default BOOLEAN NOT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_6956883F77153098 ON currency (code)'
        );

        $eurId = new Ulid();

        $this->addSql(
            'INSERT INTO currency (
                id,
                code,
                name,
                symbol,
                decimal_places,
                active,
                is_default
            ) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $eurId->toRfc4122(),
                'EUR',
                'Euro',
                '€',
                2,
                true,
                true,
            ],
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency');
    }
}
