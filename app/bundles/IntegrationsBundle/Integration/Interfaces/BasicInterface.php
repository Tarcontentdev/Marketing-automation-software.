<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration\Interfaces;

use MailVotech\PluginBundle\Integration\UnifiedIntegrationInterface;

interface BasicInterface extends UnifiedIntegrationInterface
{
    /**
     * Return the integration's name.
     */
    public function getName(): string;

    public function getIcon(): string;
}
