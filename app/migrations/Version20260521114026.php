<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use MailVotech\CoreBundle\Doctrine\PreUpAssertionMigration;
use MailVotech\LeadBundle\Entity\Company;

final class Version20260521114026 extends PreUpAssertionMigration
{
    protected const TABLE_NAME = Company::TABLE_NAME;

    protected function preUpAssertions(): void
    {
        $this->skipAssertion(function (Schema $schema) {
            return $schema->getTable($this->getPrefixedTableName())->hasColumn('deleted');
        }, 'Deleted column already added in '.self::TABLE_NAME);
    }

    public function up(Schema $schema): void
    {
        $schema->getTable($this->getPrefixedTableName())
            ->addColumn('deleted', Types::DATETIME_MUTABLE)->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable($this->getPrefixedTableName())
            ->dropColumn('deleted');
    }
}
