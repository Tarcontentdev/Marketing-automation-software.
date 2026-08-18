<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Week;

use MailVotech\CoreBundle\Helper\DateTimeHelper;

final class DateWeekNext extends DateWeekAbstract
{
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper): void
    {
        $dateTimeHelper->setDateTime('midnight monday next week', null);
    }
}
