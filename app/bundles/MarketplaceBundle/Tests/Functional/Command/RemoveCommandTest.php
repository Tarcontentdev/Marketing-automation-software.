<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Tests\Functional\Command;

use MailVotech\CoreBundle\Helper\ComposerHelper;
use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\MarketplaceBundle\Command\RemoveCommand;
use MailVotech\MarketplaceBundle\DTO\ConsoleOutput;
use Psr\Log\LoggerInterface;

final class RemoveCommandTest extends AbstractMailVotechTestCase
{
    private string $packageName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->packageName = 'koco/mailvotech-recaptcha-bundle';
    }

    public function testRemoveCommand(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutput(0, 'OK'));
        $composer->method('getMailVotechPluginPackages')
            ->willReturn(['koco/mailvotech-recaptcha-bundle']);
        $command = new RemoveCommand($composer, $this->createStub(LoggerInterface::class));

        $result = $this->testSymfonyCommand(
            'mailvotech:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        $this->assertSame(0, $result->getStatusCode());
    }

    public function testRemoveCommandWithInvalidPackageType(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutput(0, 'OK'));
        $composer->method('getMailVotechPluginPackages')
            ->willReturn([]);
        $command = new RemoveCommand($composer, $this->createStub(LoggerInterface::class));

        $result = $this->testSymfonyCommand(
            'mailvotech:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        $this->assertSame(1, $result->getStatusCode());
    }

    public function testRemoveCommandWithComposerError(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutput(1, 'Error while removing package'));
        $composer->method('getMailVotechPluginPackages')
            ->willReturn([]);
        $command = new RemoveCommand($composer, $this->createStub(LoggerInterface::class));

        $result = $this->testSymfonyCommand(
            'mailvotech:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        $this->assertSame(1, $result->getStatusCode());
    }
}
