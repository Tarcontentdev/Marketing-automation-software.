<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials;

interface CodeInterface
{
    public function getCode(): ?string;
}
