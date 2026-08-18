<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Auth\Provider\Oauth1aTwoLegged;

use MailVotech\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface CredentialsInterface extends AuthCredentialsInterface
{
    public function getAuthUrl(): string;

    public function getConsumerKey(): ?string;

    public function getConsumerSecret(): ?string;

    public function getToken(): ?string;

    public function getTokenSecret(): ?string;
}
