<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MailVotech\CoreBundle\Doctrine\PreUpAssertionMigration;

final class Version20240725105507 extends PreUpAssertionMigration
{
    protected function preUpAssertions(): void
    {
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE {$this->prefix}leads SET `country` = 'Türkiye' WHERE `country` = 'Turkey'");
        $this->addSql("UPDATE {$this->prefix}companies SET `companycountry` = 'Türkiye' WHERE `companycountry` = 'Turkey'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE {$this->prefix}leads SET `country` = 'Turkey' WHERE `country` = 'Türkiye'");
        $this->addSql("UPDATE {$this->prefix}companies SET `companycountry` = 'Turkey' WHERE `companycountry` = 'Türkiye'");
    }
}
