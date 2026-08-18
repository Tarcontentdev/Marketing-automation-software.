<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Exception;

final class MissingAppIDException extends \Exception
{
    protected $message = 'Missing Notification App ID';
}
