<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Exception;

final class MissingApiKeyException extends \Exception
{
    protected $message = 'Missing Notification API Key';
}
