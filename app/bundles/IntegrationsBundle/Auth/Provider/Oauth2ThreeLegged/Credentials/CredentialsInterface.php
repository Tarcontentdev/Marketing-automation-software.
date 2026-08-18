<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface CredentialsInterface extends AuthCredentialsInterface
{
    public function getAuthorizationUrl(): string;

    public function getTokenUrl(): string;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}
