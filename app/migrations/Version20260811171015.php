<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811171015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.5.1 - Create analysis dimensions and values';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE analysis_dimension (id UUID NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) DEFAULT NULL, position INT NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY (id))');

        $this->addSql('CREATE INDEX IDX_ANALYSIS_DIMENSION_POSITION ON analysis_dimension (position)');
        $this->addSql('CREATE INDEX IDX_ANALYSIS_DIMENSION_ACTIVE ON analysis_dimension (active)');

        $this->addSql('CREATE TABLE analysis_dimension_value (id UUID NOT NULL, name VARCHAR(100) NOT NULL, position INT NOT NULL, active BOOLEAN NOT NULL, analysis_dimension_id UUID NOT NULL, parent_id UUID DEFAULT NULL, PRIMARY KEY (id))');

        $this->addSql('CREATE INDEX IDX_9BEE9174EFE2BBA9 ON analysis_dimension_value (analysis_dimension_id)');
        $this->addSql('CREATE INDEX IDX_9BEE9174727ACA70 ON analysis_dimension_value (parent_id)');
        $this->addSql('CREATE INDEX IDX_ANALYSIS_DIMENSION_VALUE_POSITION ON analysis_dimension_value (position)');
        $this->addSql('CREATE INDEX IDX_ANALYSIS_DIMENSION_VALUE_ACTIVE ON analysis_dimension_value (active)');

        $this->addSql('ALTER TABLE analysis_dimension_value ADD CONSTRAINT FK_9BEE9174EFE2BBA9 FOREIGN KEY (analysis_dimension_id) REFERENCES analysis_dimension (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE analysis_dimension_value ADD CONSTRAINT FK_9BEE9174727ACA70 FOREIGN KEY (parent_id) REFERENCES analysis_dimension_value (id) ON DELETE RESTRICT NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE analysis_dimension_value DROP CONSTRAINT FK_9BEE9174EFE2BBA9');
        $this->addSql('ALTER TABLE analysis_dimension_value DROP CONSTRAINT FK_9BEE9174727ACA70');

        $this->addSql('DROP TABLE analysis_dimension_value');
        $this->addSql('DROP TABLE analysis_dimension');
    }
}
