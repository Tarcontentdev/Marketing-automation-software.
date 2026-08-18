<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface PasswordCredentialsGrantInterface extends AuthCredentialsInterface
{
    public function getAuthorizationUrl(): string;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;

    public function getUsername(): ?string;

    public function getPassword(): ?string;
}
