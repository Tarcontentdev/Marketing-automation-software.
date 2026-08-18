<?php

declare(strict_types=1);

namespace MailVotech\InstallBundle\Exception;

final class AlreadyInstalledException extends \Exception
{
    protected $message = 'MailVotech is already installed.';
}
