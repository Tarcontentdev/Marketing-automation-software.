<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Day;

use MailVotech\CoreBundle\Helper\DateTimeHelper;

final class DateDayYesterday extends DateDayAbstract
{
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper): void
    {
        $dateTimeHelper->modify('midnight yesterday');
    }
}
