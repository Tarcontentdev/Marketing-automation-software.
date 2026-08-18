<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class WeakPasswordException extends AuthenticationException
{
}
