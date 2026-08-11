<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811144059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.3.1 - Create categories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category (id UUID NOT NULL, name VARCHAR(100) NOT NULL, position INT NOT NULL, active BOOLEAN NOT NULL, parent_id UUID DEFAULT NULL, PRIMARY KEY (id))');

        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE INDEX IDX_CATEGORY_POSITION ON category (position)');
        $this->addSql('CREATE INDEX IDX_CATEGORY_ACTIVE ON category (active)');

        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE RESTRICT NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70');
        $this->addSql('DROP TABLE category');
    }
}
