<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use MailVotech\IntegrationsBundle\Auth\Support\Oauth2\Token\TokenFactoryInterface;

interface ConfigTokenFactoryInterface extends AuthConfigInterface
{
    public function getTokenFactory(): TokenFactoryInterface;
}
