<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Helper;

use MailVotech\CoreBundle\Helper\CommandHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class CommandHelperTest extends MailVotechMysqlTestCase
{
    private CommandHelper $commandHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandHelper = self::getContainer()->get(CommandHelper::class);
    }

    public function testRunCommandWithParam(): void
    {
        $response = $this->commandHelper->runCommand('help', ['--version']);
        $this->assertSame(0, $response->getStatusCode());
        $this->assertStringContainsString('(env: test, debug: false)', $response->getMessage());
    }

    public function testRunCommandWithoutParam(): void
    {
        $response = $this->commandHelper->runCommand('list');
        $this->assertSame(0, $response->getStatusCode());
        $this->assertStringContainsString('doctrine:database:create', $response->getMessage());
    }
}
