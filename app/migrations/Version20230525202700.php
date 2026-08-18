<?php

declare(strict_types=1);

namespace MailVotech\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MailVotech\CoreBundle\Doctrine\AbstractMailVotechMigration;

final class Version20230525202700 extends AbstractMailVotechMigration
{
    public function up(Schema $schema): void
    {
        $oldAndNewValues = [
            'Niederosterreich'   => 'Niederösterreich',
            'Oberosterreich'     => 'Oberösterreich',
            'Geneva'             => 'Genève',
            'Graubunden'         => 'Graubünden',
            'Neuchatel'          => 'Neuchâtel',
            'Sankt Gallen'       => 'St. Gallen',
            'Zurich'             => 'Zürich',
            'Baden-Wuerttemberg' => 'Baden-Württemberg',
            'Thueringen'         => 'Thüringen',
        ];

        foreach ($oldAndNewValues as $old => $new) {
            $this->addSql("UPDATE `{$this->prefix}leads` SET `state` = '{$new}' WHERE `state` = '{$old}'");
        }
    }
}
