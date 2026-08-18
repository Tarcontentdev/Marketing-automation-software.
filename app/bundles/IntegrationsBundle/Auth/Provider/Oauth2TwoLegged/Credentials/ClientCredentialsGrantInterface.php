<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface ClientCredentialsGrantInterface extends AuthCredentialsInterface
{
    public function getAuthorizationUrl(): string;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}
