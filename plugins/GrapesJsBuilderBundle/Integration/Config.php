<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Integration;

use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\IntegrationsHelper;
use MailVotech\PluginBundle\Entity\Integration;

class Config
{
    public function __construct(
        private readonly IntegrationsHelper $integrationsHelper,
    ) {
    }

    public function isPublished(): bool
    {
        try {
            $integration = $this->getIntegrationEntity();

            return (bool) $integration->getIsPublished();
        } catch (IntegrationNotFoundException) {
            return false;
        }
    }

    /**
     * @return mixed[]
     */
    public function getFeatureSettings(): array
    {
        try {
            $integration = $this->getIntegrationEntity();

            return $integration->getFeatureSettings() ?: [];
        } catch (IntegrationNotFoundException) {
            return [];
        }
    }

    /**
     * @throws IntegrationNotFoundException
     */
    public function getIntegrationEntity(): Integration
    {
        $integrationObject = $this->integrationsHelper->getIntegration(GrapesJsBuilderIntegration::NAME);

        return $integrationObject->getIntegrationConfiguration();
    }
}
