<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator\Date\Year;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionAbstract;

abstract class DateYearAbstract extends DateOptionAbstract
{
    /**
     * @return string
     */
    protected function getModifierForBetweenRange()
    {
        return '+1 year';
    }

    protected function getValueForBetweenRange(DateTimeHelper $dateTimeHelper)
    {
        return $dateTimeHelper->toLocalString('Y-%');
    }

    protected function getOperatorForBetweenRange(ContactSegmentFilterCrate $leadSegmentFilterCrate)
    {
        return '!=' === $leadSegmentFilterCrate->getOperator() ? 'notLike' : 'like';
    }
}
