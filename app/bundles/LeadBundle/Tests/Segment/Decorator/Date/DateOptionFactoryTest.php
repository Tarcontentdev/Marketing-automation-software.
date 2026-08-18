<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Segment\Decorator\Date;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionFactory;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayToday;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayTomorrow;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayYesterday;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Month\DateMonthThis;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateAnniversary;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateDefault;
use MailVotech\LeadBundle\Segment\Decorator\Date\Other\DateRelativeInterval;
use MailVotech\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Week\DateWeekThis;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearLast;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearNext;
use MailVotech\LeadBundle\Segment\Decorator\Date\Year\DateYearThis;
use MailVotech\LeadBundle\Segment\Decorator\DateDecorator;
use MailVotech\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use MailVotech\LeadBundle\Segment\RelativeDate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DateOptionFactory::class)]
final class DateOptionFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testBirthday(): void
    {
        $filterName = 'birthday';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateAnniversary::class, $filterDecorator);

        $filterName = 'anniversary';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateAnniversary::class, $filterDecorator);
    }

    public function testDayToday(): void
    {
        $filterName = 'today';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayToday::class, $filterDecorator);
    }

    public function testDayTomorrow(): void
    {
        $filterName = 'tomorrow';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayTomorrow::class, $filterDecorator);
    }

    public function testDayYesterday(): void
    {
        $filterName = 'yesterday';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDayYesterday::class, $filterDecorator);
    }

    public function testWeekLast(): void
    {
        $filterName = 'last week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekLast::class, $filterDecorator);
    }

    public function testWeekNext(): void
    {
        $filterName = 'next week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekNext::class, $filterDecorator);
    }

    public function testWeekThis(): void
    {
        $filterName = 'this week';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateWeekThis::class, $filterDecorator);
    }

    public function testMonthLast(): void
    {
        $filterName = 'last month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthLast::class, $filterDecorator);
    }

    public function testMonthNext(): void
    {
        $filterName = 'next month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthNext::class, $filterDecorator);
    }

    public function testMonthThis(): void
    {
        $filterName = 'this month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateMonthThis::class, $filterDecorator);
    }

    public function testYearLast(): void
    {
        $filterName = 'last year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearLast::class, $filterDecorator);
    }

    public function testYearNext(): void
    {
        $filterName = 'next year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearNext::class, $filterDecorator);
    }

    public function testYearThis(): void
    {
        $filterName = 'this year';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateYearThis::class, $filterDecorator);
    }

    public function testRelativePlus(): void
    {
        $filterName = '+20 days';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    public function testRelativeMinus(): void
    {
        $filterName = '+20 days';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    public function testRelativeAgo(): void
    {
        $filterName = '20 days ago';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    public function testRelativeFirstDayOf(): void
    {
        $filterName = 'first day of previous month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    public function testRelativeLastDayOf(): void
    {
        $filterName = 'last day of previous month';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateRelativeInterval::class, $filterDecorator);
    }

    /**
     * @return \Iterator<(int|string), array<string>>
     */
    public static function getRelativeDateNotations(): \Iterator
    {
        yield [DateRelativeInterval::class, 'first day of January 2021'];
        yield [DateRelativeInterval::class, 'last day of January 2021'];
        yield [DateRelativeInterval::class, '5 days ago'];
        yield [DateDefault::class, 'day of January 2021'];
    }

    #[DataProvider('getRelativeDateNotations')]
    public function testRelativeDateNotations(string $expectedResult, string $filterName): void
    {
        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf($expectedResult, $filterDecorator);
    }

    public function testDateDefault(): void
    {
        $filterName = '2018-01-01';

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDefault::class, $filterDecorator);
    }

    public function testNullValue(): void
    {
        $filterName = null;

        $filterDecorator = $this->getFilterDecorator($filterName);

        $this->assertInstanceOf(DateDefault::class, $filterDecorator);
    }

    private function getFilterDecorator(?string $filterName): FilterDecoratorInterface
    {
        $relativeDate     = $this->createMock(RelativeDate::class);

        $relativeDate->method('getRelativeDateStrings')
            ->willReturn(
                [
                    'mailvotech.lead.list.month_last'  => 'last month',
                    'mailvotech.lead.list.month_next'  => 'next month',
                    'mailvotech.lead.list.month_this'  => 'this month',
                    'mailvotech.lead.list.today'       => 'today',
                    'mailvotech.lead.list.tomorrow'    => 'tomorrow',
                    'mailvotech.lead.list.yesterday'   => 'yesterday',
                    'mailvotech.lead.list.week_last'   => 'last week',
                    'mailvotech.lead.list.week_next'   => 'next week',
                    'mailvotech.lead.list.week_this'   => 'this week',
                    'mailvotech.lead.list.year_last'   => 'last year',
                    'mailvotech.lead.list.year_next'   => 'next year',
                    'mailvotech.lead.list.year_this'   => 'this year',
                    'mailvotech.lead.list.birthday'    => 'birthday',
                    'mailvotech.lead.list.anniversary' => 'anniversary',
                ]
            );

        $dateOptionFactory = new DateOptionFactory($this->createStub(DateDecorator::class), $relativeDate, $this->createStub(TimezoneResolver::class));

        $filter                    = [
            'glue'     => 'and',
            'type'     => 'datetime',
            'object'   => 'lead',
            'field'    => 'date_identified',
            'operator' => '=',
            'filter'   => $filterName,
            'display'  => null,
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);

        return $dateOptionFactory->getDateOption($contactSegmentFilterCrate);
    }
}
