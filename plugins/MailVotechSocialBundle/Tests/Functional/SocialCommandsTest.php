<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class SocialCommandsTest extends MailVotechMysqlTestCase
{
    public function testSocialMonitoringCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('mailvotech:social:monitoring');

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertSame("No published monitors found. Make sure the id you supplied is published\n", $commandTester->getDisplay());
    }

    public function testTwitterHashtagsCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('social:monitor:twitter:hashtags');

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertSame("Twitter plugin not published!\n", $commandTester->getDisplay());
    }

    public function testTwitterMentionsCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('social:monitor:twitter:mentions');

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertSame("Twitter plugin not published!\n", $commandTester->getDisplay());
    }
}
