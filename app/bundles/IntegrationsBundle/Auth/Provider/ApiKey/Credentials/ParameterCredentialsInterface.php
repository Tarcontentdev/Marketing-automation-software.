<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\ApiKey\Credentials;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface ParameterCredentialsInterface extends AuthCredentialsInterface
{
    public function getKeyName(): string;

    public function getApiKey(): ?string;
}
