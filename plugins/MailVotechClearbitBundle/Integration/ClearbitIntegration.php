<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechClearbitBundle\Integration;

use MailVotech\IntegrationsBundle\Integration\BasicIntegration;
use MailVotech\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class ClearbitIntegration extends BasicIntegration implements BasicInterface
{
    public function getName(): string
    {
        return 'Clearbit';
    }

    public function getDisplayName(): string
    {
        return 'Clearbit';
    }

    public function getIcon(): string
    {
        return 'plugins/MailVotechClearbitBundle/Assets/img/clearbit.png';
    }

    public function shouldAutoUpdate(): bool
    {
        $apiKeys = $this->getIntegrationSettings()?->getApiKeys() ?? [];

        return isset($apiKeys['auto_update']) && (bool) $apiKeys['auto_update'];
    }
}
