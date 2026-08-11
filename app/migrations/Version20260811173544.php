<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811173544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'v0.6.5.2 - Create analysis dimension assignments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE analysis_dimension_assignment (
                id UUID NOT NULL,
                analysis_id UUID NOT NULL,
                analysis_dimension_value_id UUID NOT NULL,
                PRIMARY KEY (id)
            )'
        );

        $this->addSql(
            'CREATE INDEX IDX_FC6801CD7941003F
            ON analysis_dimension_assignment (analysis_id)'
        );

        $this->addSql(
            'CREATE INDEX IDX_FC6801CD1A517103
            ON analysis_dimension_assignment (analysis_dimension_value_id)'
        );

        $this->addSql(
            'CREATE UNIQUE INDEX UNIQ_ANALYSIS_DIMENSION_ASSIGNMENT
            ON analysis_dimension_assignment (
                analysis_id,
                analysis_dimension_value_id
            )'
        );

        $this->addSql(
            'ALTER TABLE analysis_dimension_assignment
            ADD CONSTRAINT FK_FC6801CD7941003F
            FOREIGN KEY (analysis_id)
            REFERENCES analysis (id)
            ON DELETE RESTRICT NOT DEFERRABLE'
        );

        $this->addSql(
            'ALTER TABLE analysis_dimension_assignment
            ADD CONSTRAINT FK_FC6801CD1A517103
            FOREIGN KEY (analysis_dimension_value_id)
            REFERENCES analysis_dimension_value (id)
            ON DELETE RESTRICT NOT DEFERRABLE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE analysis_dimension_assignment
            DROP CONSTRAINT FK_FC6801CD7941003F'
        );

        $this->addSql(
            'ALTER TABLE analysis_dimension_assignment
            DROP CONSTRAINT FK_FC6801CD1A517103'
        );

        $this->addSql(
            'DROP TABLE analysis_dimension_assignment'
        );
    }
}
