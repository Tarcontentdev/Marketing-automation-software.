<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechGmailBundle\Tests\Unit\Integration;

use MailVotechPlugin\MailVotechGmailBundle\Integration\GmailIntegration;
use PHPUnit\Framework\TestCase;

final class GmailIntegrationTest extends TestCase
{
    private GmailIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new GmailIntegration();
    }

    public function testGetNameReturnsGmail(): void
    {
        $this->assertSame('Gmail', $this->integration->getName());
    }

    public function testGetDisplayNameReturnsGmail(): void
    {
        $this->assertSame('Gmail', $this->integration->getDisplayName());
    }

    public function testGetIconReturnsExpectedPath(): void
    {
        $this->assertSame('plugins/MailVotechGmailBundle/Assets/img/gmail.png', $this->integration->getIcon());
    }
}
