<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20230311195347 extends AbstractMailVotechMigration
{
    public const BATCH_SIZE = 1000;

    public function up(Schema $schema): void
    {
        $tableName  = MAILVOTECH_TABLE_PREFIX.'integration_entity';
        $columnName = 'integration';
        $value      = 'Pipedrive';

        $rowCount = self::BATCH_SIZE;

        while ($rowCount) {
            $sql      = "DELETE FROM {$tableName} WHERE {$columnName} = :value LIMIT ".self::BATCH_SIZE;
            $rowCount = $this->connection->executeStatement($sql, ['value' => $value]);
        }
    }
}
