<?php

namespace MailVotech\LeadBundle\Segment\Decorator\Date;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayToday;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayTomorrow;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayYesterday;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateDefault;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekThis;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearThis;
use MailVotech\LeadBundle\Segment\Decorator\DateDecorator;
use MailVotech\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use MailVotech\LeadBundle\Segment\RelativeDate;

class DateOptionFactory
{
    public function __construct(
        private readonly DateDecorator $dateDecorator,
        private readonly RelativeDate $relativeDate,
        private readonly TimezoneResolver $timezoneResolver,
    ) {
    }

    public function getDateOption(ContactSegmentFilterCrate $leadSegmentFilterCrate): FilterDecoratorInterface
    {
        $originalValue        = $leadSegmentFilterCrate->getFilter();
        $relativeDateStrings  = $this->relativeDate->getRelativeDateStrings();
        $dateOptionParameters = new DateOptionParameters($leadSegmentFilterCrate, $relativeDateStrings, $this->timezoneResolver);
        $timeframe            = $dateOptionParameters->getTimeframe();

        if (!$timeframe) {
            return new DateDefault($this->dateDecorator, $originalValue);
        }

        switch ($timeframe) {
            case 'birthday':
            case 'anniversary':
            case str_contains($timeframe, 'anniversary')
                || str_contains($timeframe, 'birthday'):
                return new DateAnniversary($this->dateDecorator, $dateOptionParameters);
            case 'today':
                return new DateDayToday($this->dateDecorator, $dateOptionParameters);
            case 'tomorrow':
                return new DateDayTomorrow($this->dateDecorator, $dateOptionParameters);
            case 'yesterday':
                return new DateDayYesterday($this->dateDecorator, $dateOptionParameters);
            case 'week_last':
                return new DateWeekLast($this->dateDecorator, $dateOptionParameters);
            case 'week_next':
                return new DateWeekNext($this->dateDecorator, $dateOptionParameters);
            case 'week_this':
                return new DateWeekThis($this->dateDecorator, $dateOptionParameters);
            case 'month_last':
                return new DateMonthLast($this->dateDecorator, $dateOptionParameters);
            case 'month_next':
                return new DateMonthNext($this->dateDecorator, $dateOptionParameters);
            case 'month_this':
                return new DateMonthThis($this->dateDecorator, $dateOptionParameters);
            case 'year_last':
                return new DateYearLast($this->dateDecorator, $dateOptionParameters);
            case 'year_next':
                return new DateYearNext($this->dateDecorator, $dateOptionParameters);
            case 'year_this':
                return new DateYearThis($this->dateDecorator, $dateOptionParameters);
            case str_contains($timeframe[0], '-') // -5 days
                || str_contains($timeframe[0], '+') // +5 days
                || $this->isRelativeFormatsPresent($timeframe):
                return new DateRelativeInterval($this->dateDecorator, $originalValue, $dateOptionParameters);
            default:
                return new DateDefault($this->dateDecorator, $originalValue);
        }
    }

    protected function isRelativeFormatsPresent(string $timeframe): bool
    {
        $notations = [
            'first day of ', // first day of January 2021
            'last day of ', // last day of January 2021
            ' ago', // 5 days ago
        ];

        foreach ($notations as $notation) {
            if (str_contains($timeframe, $notation)) {
                return true;
            }
        }

        return false;
    }
}
