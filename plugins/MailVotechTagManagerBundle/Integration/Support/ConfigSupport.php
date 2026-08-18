<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Integration\Support;

use MailVotech\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MailVotechPlugin\MailVotechTagManagerBundle\Integration\TagManagerIntegration;

final class ConfigSupport extends TagManagerIntegration implements ConfigFormInterface
{
    use DefaultConfigFormTrait;
}
