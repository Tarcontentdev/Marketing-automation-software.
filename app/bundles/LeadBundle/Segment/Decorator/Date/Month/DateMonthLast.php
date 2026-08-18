<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Month;

use MailVotech\CoreBundle\Helper\DateTimeHelper;

final class DateMonthLast extends DateMonthAbstract
{
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper): void
    {
        $dateTimeHelper->setDateTime('midnight first day of last month', null);
    }
}
