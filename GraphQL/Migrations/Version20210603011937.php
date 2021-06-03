<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210603011937 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '0044. Add view_playbill_sessions';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
            <<<SQL
                CREATE OR REPLACE VIEW view_playbill_sessions AS
                    SELECT id
                    FROM sessions
                    WHERE kinoplan_session_date >= NOW() AND enabled = 1 AND MONTH(kinoplan_session_date) = (
                        SELECT MIN(MONTH(kinoplan_session_date))
                        FROM sessions
                        WHERE kinoplan_session_date >= NOW() AND enabled = 1
                    )
                    ORDER BY kinoplan_session_date;
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP VIEW view_playbill_sessions');
    }
}
