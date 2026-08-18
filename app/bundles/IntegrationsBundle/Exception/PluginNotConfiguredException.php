<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Exception;

final class PluginNotConfiguredException extends \Exception
{
    protected $message = 'mailvotech.integration.not_configured';
}
