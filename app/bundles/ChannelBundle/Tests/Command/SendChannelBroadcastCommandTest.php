<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class SendChannelBroadcastCommandTest extends MailVotechMysqlTestCase
{
    public function testBroadcastCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('mailvotech:broadcasts:send');
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testBroadcastCommandWithLimit(): void
    {
        $commandTester = $this->testSymfonyCommand('mailvotech:broadcasts:send', ['--limit' => 1]);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
