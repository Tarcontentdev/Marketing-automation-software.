<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Day;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionAbstract;

abstract class DateDayAbstract extends DateOptionAbstract
{
    /**
     * @return string
     */
    protected function getModifierForBetweenRange()
    {
        return '+1 day';
    }

    protected function getValueForBetweenRange(DateTimeHelper $dateTimeHelper)
    {
        return $dateTimeHelper->toLocalString('Y-m-d%');
    }

    protected function getOperatorForBetweenRange(ContactSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return '!=' === $leadSegmentFilterCrate->getOperator() ? 'notLike' : 'like';
    }
}
