<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\PluginBundle\Entity\Integration;
use Symfony\Contracts\EventDispatcher\Event;

final class ConfigAuthUrlEvent extends Event
{
    public function __construct(
        private readonly Integration $integrationConfiguration,
        private string $authUrl,
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

    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    public function setAuthUrl(string $authUrl): void
    {
        $this->authUrl = $authUrl;
    }
}
