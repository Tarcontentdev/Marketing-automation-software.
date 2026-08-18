<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Event;

use MailVotech\PluginBundle\Integration\UnifiedIntegrationInterface;

final class PluginIntegrationAuthCallbackUrlEvent extends AbstractPluginIntegrationEvent
{
    /**
     * @param string $callbackUrl
     */
    public function __construct(
        UnifiedIntegrationInterface $integration,
        private $callbackUrl,
    ) {
        $this->integration = $integration;
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;

        $this->stopPropagation();
    }
}
