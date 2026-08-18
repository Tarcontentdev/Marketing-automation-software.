<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration\Interfaces;

interface ConfigFormAuthorizeButtonInterface
{
    public function isAuthorized(): bool;

    public function getAuthorizationUrl(): string;
}
