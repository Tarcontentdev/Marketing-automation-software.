<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Segment\Decorator\Date\Day;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionParameters;
use MailVotech\LeadBundle\Segment\Decorator\Date\Day\DateDayTomorrow;
use MailVotech\LeadBundle\Segment\Decorator\Date\TimezoneResolver;
use MailVotech\LeadBundle\Segment\Decorator\DateDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(DateDayTomorrow::class)]
final class DateDayTomorrowTest extends \PHPUnit\Framework\TestCase
{
    public function testGetOperatorBetween(): void
    {
        $dateDecorator    = $this->createStub(DateDecorator::class);
        $timezoneResolver = $this->createStub(TimezoneResolver::class);

        $filter        = [
            'operator' => '=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateDayTomorrow($dateDecorator, $dateOptionParameters);

        $this->assertEquals('like', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    public function testGetOperatorLessOrEqual(): void
    {
        $dateDecorator    = $this->createMock(DateDecorator::class);
        $timezoneResolver = $this->createStub(TimezoneResolver::class);

        $dateDecorator->method('getOperator')
            ->with()
            ->willReturn('=<');

        $filter        = [
            'operator' => 'lte',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateDayTomorrow($dateDecorator, $dateOptionParameters);

        $this->assertEquals('=<', $filterDecorator->getOperator($contactSegmentFilterCrate));
    }

    public function testGetParameterValueBetween(): void
    {
        $dateDecorator    = $this->createStub(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter        = [
            'operator' => '!=',
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateDayTomorrow($dateDecorator, $dateOptionParameters);

        $this->assertEquals('2018-03-03%', $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    #[DataProvider('dataProviderForOperatorAndType')]
    public function testGetParameterValueSingle(string $operator, string $type, string $expectedDateValue): void
    {
        $dateDecorator    = $this->createStub(DateDecorator::class);
        $timezoneResolver = $this->createMock(TimezoneResolver::class);

        $date = new DateTimeHelper('2018-03-02 08:00:09', null, 'local');

        $timezoneResolver->method('getDefaultDate')
            ->with()
            ->willReturn($date);

        $filter        = [
            'operator' => $operator,
            'type'     => $type,
        ];
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate($filter);
        $dateOptionParameters      = new DateOptionParameters($contactSegmentFilterCrate, [], $timezoneResolver);

        $filterDecorator = new DateDayTomorrow($dateDecorator, $dateOptionParameters);

        $this->assertEquals($expectedDateValue, $filterDecorator->getParameterValue($contactSegmentFilterCrate));
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderForOperatorAndType(): iterable
    {
        yield ['lt', 'date', '2018-03-03'];
        yield ['lte', 'date', '2018-03-03'];
        yield ['gt', 'date', '2018-03-03'];
        yield ['gte', 'date', '2018-03-03'];
        yield ['lt', 'datetime', '2018-03-03 00:00:00'];
        yield ['lte', 'datetime', '2018-03-03 23:59:59'];
        yield ['gt', 'datetime', '2018-03-03 23:59:59'];
        yield ['gte', 'datetime', '2018-03-03 00:00:00'];
    }
}
