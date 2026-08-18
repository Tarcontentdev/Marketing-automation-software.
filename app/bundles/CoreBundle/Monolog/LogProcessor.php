<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Monolog;

use Monolog\LogRecord;

final class LogProcessor
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['hostname'] = gethostname();
        $record->extra['pid']      = getmypid();

        return $record;
    }
}
