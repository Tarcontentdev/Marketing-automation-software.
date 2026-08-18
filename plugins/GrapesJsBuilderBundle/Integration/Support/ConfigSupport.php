<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Integration\Support;

use MailVotech\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MailVotechPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration;

final class ConfigSupport extends GrapesJsBuilderIntegration implements ConfigFormInterface
{
    use DefaultConfigFormTrait;
}
