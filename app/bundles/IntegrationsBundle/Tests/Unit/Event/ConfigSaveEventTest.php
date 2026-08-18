<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Event;

use MailVotech\IntegrationsBundle\Event\ConfigSaveEvent;
use MailVotech\PluginBundle\Entity\Integration;
use PHPUnit\Framework\TestCase;

final class ConfigSaveEventTest extends TestCase
{
    public function testGetters(): void
    {
        $name        = 'name';
        $integration = $this->createMock(Integration::class);
        $event       = new ConfigSaveEvent($integration);

        $integration->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $this->assertSame($integration, $event->getIntegrationConfiguration());
        $this->assertSame($name, $event->getIntegration());
    }
}
