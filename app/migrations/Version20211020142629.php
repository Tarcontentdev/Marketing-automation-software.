<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20211020142629 extends AbstractMailVotechMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE {$this->getPrefixedTableName('leads')} SET date_modified = date_added WHERE date_modified IS NULL;");
    }
}
