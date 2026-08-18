<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20221014061125 extends AbstractMailVotechMigration
{
    public function preUp(Schema $schema): void
    {
        if ($schema->getTable($this->getTableName())->hasIndex($this->getIndexName())) {
            throw new SkipMigration(sprintf('Index %s already exists', $this->getIndexName()));
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql(sprintf('CREATE INDEX %s ON %s (webhook_id, date_added)', $this->getIndexName(), $this->getTableName()));
    }

    private function getTableName(): string
    {
        return "{$this->prefix}webhook_logs";
    }

    private function getIndexName(): string
    {
        return "{$this->prefix}webhook_id_date_added";
    }
}
