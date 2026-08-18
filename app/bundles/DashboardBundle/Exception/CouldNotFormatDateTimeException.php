<?php

declare(strict_types=1);

namespace MailVotech\DashboardBundle\Exception;

final class CouldNotFormatDateTimeException extends \Exception
{
    public function __construct(
        string $message = 'Can\'t format date object to string',
        int $code = 0,
        ?\Throwable $throwable = null,
    ) {
        parent::__construct($message, $code, $throwable);
    }
}
