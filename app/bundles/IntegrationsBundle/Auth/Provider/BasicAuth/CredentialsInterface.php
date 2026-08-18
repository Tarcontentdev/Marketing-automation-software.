<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\BasicAuth;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface CredentialsInterface extends AuthCredentialsInterface
{
    public function getUsername(): ?string;

    public function getPassword(): ?string;
}
