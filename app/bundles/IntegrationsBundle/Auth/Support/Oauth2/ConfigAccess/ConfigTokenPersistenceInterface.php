<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess;

use kamermans\OAuth2\Persistence\TokenPersistenceInterface as KamermansTokenPersistenceInterface;
use MailVotech\IntegrationsBundle\Auth\Provider\AuthConfigInterface;

interface ConfigTokenPersistenceInterface extends AuthConfigInterface
{
    public function getTokenPersistence(): KamermansTokenPersistenceInterface;
}
