<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Event;

use MailVotech\PluginBundle\Integration\UnifiedIntegrationInterface;

final class PluginIntegrationAuthRedirectEvent extends AbstractPluginIntegrationEvent
{
    /**
     * @param string $authUrl
     */
    public function __construct(
        UnifiedIntegrationInterface $integration,
        private $authUrl,
    ) {
        $this->integration = $integration;
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->authUrl;
    }

    /**
     * @param string $authUrl
     */
    public function setAuthUrl($authUrl): void
    {
        $this->authUrl = $authUrl;

        $this->stopPropagation();
    }
}
