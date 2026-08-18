<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechGmailBundle\Tests\Unit\Integration\Support;

use MailVotechPlugin\MailVotechGmailBundle\Form\Type\GmailKeysType;
use MailVotechPlugin\MailVotechGmailBundle\Integration\Support\ConfigSupport;
use PHPUnit\Framework\TestCase;

final class ConfigSupportTest extends TestCase
{
    public function testGetAuthConfigFormNameReturnsGmailKeysType(): void
    {
        $configSupport = new ConfigSupport();

        $this->assertSame(GmailKeysType::class, $configSupport->getAuthConfigFormName());
    }
}
