<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Week;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionAbstract;

abstract class DateWeekAbstract extends DateOptionAbstract
{
    /**
     * @return string
     */
    protected function getModifierForBetweenRange()
    {
        return '+1 week';
    }

    protected function getValueForBetweenRange(DateTimeHelper $dateTimeHelper)
    {
        $dateFormat = $this->dateOptionParameters->hasTimePart() ? 'Y-m-d H:i:s' : 'Y-m-d';
        $startWith  = $dateTimeHelper->toLocalString($dateFormat);

        $modifier = $this->getModifierForBetweenRange().' -1 second';
        $dateTimeHelper->modify($modifier);
        $endWith = $dateTimeHelper->toLocalString($dateFormat);

        return [$startWith, $endWith];
    }

    protected function getOperatorForBetweenRange(ContactSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return '!=' === $leadSegmentFilterCrate->getOperator() ? 'notBetween' : 'between';
    }
}
