<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\PluginBundle\Entity\Integration;
use Symfony\Contracts\EventDispatcher\Event;

final class ConfigSaveEvent extends Event
{
    public function __construct(
        private readonly Integration $integrationConfiguration,
    ) {
    }

    public function getIntegrationConfiguration(): Integration
    {
        return $this->integrationConfiguration;
    }

    public function getIntegration(): string
    {
        return $this->integrationConfiguration->getName();
    }
}
