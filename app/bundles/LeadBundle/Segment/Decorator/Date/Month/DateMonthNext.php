<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Month;

use MailVotech\CoreBundle\Helper\DateTimeHelper;

final class DateMonthNext extends DateMonthAbstract
{
    protected function modifyBaseDate(DateTimeHelper $dateTimeHelper): void
    {
        $dateTimeHelper->setDateTime('midnight first day of next month', null);
    }
}
