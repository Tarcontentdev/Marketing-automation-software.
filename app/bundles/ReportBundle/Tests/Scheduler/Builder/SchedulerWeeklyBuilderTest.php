<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Scheduler\Builder;

use MailVotech\ReportBundle\Scheduler\Builder\SchedulerWeeklyBuilder;
use MailVotech\ReportBundle\Scheduler\Entity\SchedulerEntity;
use MailVotech\ReportBundle\Scheduler\Enum\SchedulerEnum;
use MailVotech\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use Recurr\Exception\InvalidArgument;
use Recurr\Rule;

final class SchedulerWeeklyBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testBuilEvent(): void
    {
        $schedulerDailyBuilder = new SchedulerWeeklyBuilder();

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);

        $startDate = (new \DateTime())->setTime(0, 0)->modify('+1 day');
        $rule      = new Rule();
        $rule->setStartDate($startDate)
            ->setCount(1);

        $schedulerDailyBuilder->build($rule, $schedulerEntity);

        $this->assertEquals(Rule::$freqs['WEEKLY'], $rule->getFreq());
    }

    public function testBuilEventFails(): void
    {
        $schedulerDailyBuilder = new SchedulerWeeklyBuilder();

        $schedulerEntity = new SchedulerEntity(true, SchedulerEnum::UNIT_DAILY, null, null);

        $rule = $this->createMock(Rule::class);

        $rule->expects($this->once())
            ->method('setFreq')
            ->with('WEEKLY')
            ->willThrowException(new InvalidArgument());

        $this->expectException(InvalidSchedulerException::class);

        $schedulerDailyBuilder->build($rule, $schedulerEntity);
    }
}
