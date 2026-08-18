<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Year;

use MailVotech\CoreBundle\Helper\DateTimeHelper;

final class DateYearNext extends DateYearAbstract
{
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper): void
    {
        $dateTimeHelper->setDateTime('midnight first day of January next year', null);
    }
}
