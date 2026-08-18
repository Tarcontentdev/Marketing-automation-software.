<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Support\Oauth2\Token;

use kamermans\OAuth2\Token\RawToken;
use MailVotech\IntegrationsBundle\Helper\IntegrationsHelper;
use MailVotech\PluginBundle\Entity\Integration;

final readonly class TokenPersistenceFactory
{
    public function __construct(
        private IntegrationsHelper $integrationsHelper,
    ) {
    }

    public function create(Integration $integration): TokenPersistence
    {
        $tokenPersistence = new TokenPersistence($this->integrationsHelper);

        $tokenPersistence->setIntegration($integration);

        $apiKeys = $integration->getApiKeys();

        $token = new RawToken(
            $apiKeys['access_token'] ?? null,
            $apiKeys['refresh_token'] ?? null,
            $apiKeys['expires_at'] ?? null
        );

        $tokenPersistence->restoreToken($token);

        return $tokenPersistence;
    }
}
