<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Integration\Support;

use MailVotech\IntegrationsBundle\Integration\Interfaces\BuilderInterface;
use MailVotechPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration;

final class BuilderSupport extends GrapesJsBuilderIntegration implements BuilderInterface
{
    /**
     * @var string[]
     */
    private array $featuresSupported = ['email', 'page'];

    public function isSupported(string $featureName): bool
    {
        return in_array($featureName, $this->featuresSupported);
    }
}
