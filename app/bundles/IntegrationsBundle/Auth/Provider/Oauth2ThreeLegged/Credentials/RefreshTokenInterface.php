<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface RefreshTokenInterface extends AuthCredentialsInterface
{
    public function getRefreshToken(): ?string;
}
