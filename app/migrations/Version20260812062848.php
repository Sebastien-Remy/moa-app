<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Ulid;

final class Version20260812062848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6 code cleanup - Add currency indexes and migrate user IDs to ULID';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE INDEX idx_currency_active ON currency (active)'
        );

        $this->addSql(
            'CREATE INDEX idx_currency_is_default ON currency (is_default)'
        );

        $users = $this->connection->fetchAllAssociative(
            'SELECT id FROM "user" ORDER BY id'
        );

        $this->addSql(
            'ALTER TABLE "user" ADD new_id UUID DEFAULT NULL'
        );

        foreach ($users as $user) {
            $ulid = new Ulid();

            $this->addSql(
                'UPDATE "user" SET new_id = ? WHERE id = ?',
                [
                    $ulid->toRfc4122(),
                    $user['id'],
                ],
            );
        }

        $this->addSql(
            'ALTER TABLE "user" ALTER new_id SET NOT NULL'
        );

        $this->addSql(
            'ALTER TABLE "user" DROP CONSTRAINT user_pkey'
        );

        $this->addSql(
            'ALTER TABLE "user" DROP COLUMN id'
        );

        $this->addSql(
            'ALTER TABLE "user" RENAME COLUMN new_id TO id'
        );

        $this->addSql(
            'ALTER TABLE "user" ADD PRIMARY KEY (id)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'User integer IDs cannot be restored after migration to ULID.'
        );
    }
}
