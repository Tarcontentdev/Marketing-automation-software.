<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Event;

use MailVotech\IntegrationsBundle\Event\KeysSaveEvent;
use MailVotech\PluginBundle\Entity\Integration;
use PHPUnit\Framework\TestCase;

final class KeysSaveEventTest extends TestCase
{
    public function testGetters(): void
    {
        $integration = $this->createMock(Integration::class);
        $keys        = ['apikey' => 'test'];
        $integration->expects($this->once())
            ->method('getApiKeys')
            ->willReturn($keys);

        $event = new KeysSaveEvent($integration, $keys);

        $this->assertSame($integration, $event->getIntegrationConfiguration());
        $this->assertSame($keys, $event->getOldKeys());
        $this->assertSame($keys, $event->getNewKeys());
    }
}
