<?php

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190326190241 extends AbstractMailVotechMigration
{
    /**
     * @throws SkipMigration
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema): void
    {
        if ($schema->getTable("{$this->prefix}campaign_events")->hasColumn('failed_count')) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE {$this->prefix}campaign_events ADD failed_count INT NOT NULL;");
    }
}
