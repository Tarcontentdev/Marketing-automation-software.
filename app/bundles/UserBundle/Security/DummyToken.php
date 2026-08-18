<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

final class DummyToken extends AbstractToken
{
    public function getCredentials(): string
    {
        return '';
    }
}
