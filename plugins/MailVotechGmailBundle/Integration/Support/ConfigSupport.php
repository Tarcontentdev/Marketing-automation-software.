<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechGmailBundle\Integration\Support;

use MailVotech\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MailVotechPlugin\MailVotechGmailBundle\Form\Type\GmailKeysType;
use MailVotechPlugin\MailVotechGmailBundle\Integration\GmailIntegration;

final class ConfigSupport extends GmailIntegration implements ConfigFormInterface, ConfigFormAuthInterface
{
    use DefaultConfigFormTrait;

    public function getAuthConfigFormName(): string
    {
        return GmailKeysType::class;
    }
}
