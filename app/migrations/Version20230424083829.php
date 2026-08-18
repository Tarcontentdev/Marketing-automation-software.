<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\Migrations\Exception\SkipMigration;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20230424083829 extends AbstractMailVotechMigration
{
    /**
     * @throws SkipMigration
     * @throws SchemaException
     */
    public function preUp(Schema $schema): void
    {
        $table = $schema->getTable($this->prefix.'focus');

        if ($table->hasIndex($this->prefix.'focus_name')) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX '.$this->prefix.'focus_name ON '.$this->prefix.'focus (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX '.$this->prefix.'focus_name ON '.$this->prefix.'focus');
    }
}
