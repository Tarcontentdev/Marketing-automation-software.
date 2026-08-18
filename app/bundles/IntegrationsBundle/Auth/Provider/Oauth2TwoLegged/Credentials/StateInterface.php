<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials;

interface StateInterface
{
    public function getState(): ?string;
}
