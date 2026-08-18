<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechGmailBundle\Integration;

use MailVotech\IntegrationsBundle\Integration\BasicIntegration;
use MailVotech\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class GmailIntegration extends BasicIntegration implements BasicInterface
{
    public function getName(): string
    {
        return 'Gmail';
    }

    public function getDisplayName(): string
    {
        return 'Gmail';
    }

    public function getIcon(): string
    {
        return 'plugins/MailVotechGmailBundle/Assets/img/gmail.png';
    }
}
