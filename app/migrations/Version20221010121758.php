<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20221010121758 extends AbstractMailVotechMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `{$this->prefix}leads` SET `state` = 'Uttarakhand' WHERE `state` = 'Uttaranchal'");
    }
}
