<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class ExportSchedulerCommandTest extends MailVotechMysqlTestCase
{
    public function testCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('mailvotech:reports:scheduler');

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertSame("Scheduler has finished\n", $commandTester->getDisplay());
    }
}
