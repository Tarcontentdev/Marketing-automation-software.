<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechEmailMarketingBundle\Api;

use MailVotech\PluginBundle\Integration\AbstractIntegration;
use MailVotech\PluginBundle\Integration\UnifiedIntegrationInterface;

class EmailMarketingApi
{
    protected $keys;

    /**
     * @param AbstractIntegration $integration
     */
    public function __construct(
        protected UnifiedIntegrationInterface $integration,
    ) {
        $this->keys        = $integration->getKeys();
    }
}
