<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Integration;

use MailVotech\PluginBundle\Helper\IntegrationHelper;

final readonly class Config
{
    public function __construct(
        private IntegrationHelper $integrationsHelper,
    ) {
    }

    public function isPublished(): bool
    {
        $integration = $this->integrationsHelper->getIntegrationObject(TwitterIntegration::NAME);

        return $integration && $integration->getIntegrationSettings()->getIsPublished();
    }
}
