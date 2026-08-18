<?php

namespace MailVotech\ReportBundle\Scheduler\Factory;

use MailVotech\ReportBundle\Scheduler\Builder\SchedulerDailyBuilder;
use MailVotech\ReportBundle\Scheduler\Builder\SchedulerMonthBuilder;
use MailVotech\ReportBundle\Scheduler\Builder\SchedulerNowBuilder;
use MailVotech\ReportBundle\Scheduler\Builder\SchedulerWeeklyBuilder;
use MailVotech\ReportBundle\Scheduler\BuilderInterface;
use MailVotech\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use MailVotech\ReportBundle\Scheduler\SchedulerInterface;

final class SchedulerTemplateFactory
{
    /**
     * @throws NotSupportedScheduleTypeException
     */
    public function getBuilder(SchedulerInterface $scheduler): BuilderInterface
    {
        if ($scheduler->isScheduledNow()) {
            return new SchedulerNowBuilder();
        }
        if ($scheduler->isScheduledDaily()) {
            return new SchedulerDailyBuilder();
        }
        if ($scheduler->isScheduledWeekly()) {
            return new SchedulerWeeklyBuilder();
        }
        if ($scheduler->isScheduledMonthly()) {
            return new SchedulerMonthBuilder();
        }

        throw new NotSupportedScheduleTypeException();
    }
}
