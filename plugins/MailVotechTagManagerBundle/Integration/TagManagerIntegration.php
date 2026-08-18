<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Integration;

use MailVotech\IntegrationsBundle\Integration\BasicIntegration;
use MailVotech\IntegrationsBundle\Integration\Interfaces\BasicInterface;

class TagManagerIntegration extends BasicIntegration implements BasicInterface
{
    public const PLUGIN_NAME = 'TagManager';

    public function getName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function getDisplayName(): string
    {
        return 'Tag Manager';
    }

    public function getIcon(): string
    {
        return 'plugins/MailVotechTagManagerBundle/Assets/img/tagmanager.png';
    }
}
