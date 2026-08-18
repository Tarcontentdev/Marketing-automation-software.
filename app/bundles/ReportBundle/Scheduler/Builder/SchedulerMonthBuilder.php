<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Scheduler\Builder;

use MailVotech\ReportBundle\Scheduler\BuilderInterface;
use MailVotech\ReportBundle\Scheduler\Enum\SchedulerEnum;
use MailVotech\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use MailVotech\ReportBundle\Scheduler\SchedulerInterface;
use Recurr\Exception\InvalidArgument;
use Recurr\Exception\InvalidRRule;
use Recurr\Rule;

final class SchedulerMonthBuilder implements BuilderInterface
{
    /**
     * @throws InvalidSchedulerException
     */
    public function build(Rule $rule, SchedulerInterface $scheduler): Rule
    {
        try {
            $frequency = $scheduler->getScheduleMonthFrequency();

            $rule->setFreq('MONTHLY');

            if ($scheduler->isScheduledWeekDays()) {
                $days = SchedulerEnum::getWeekDays();
            } else {
                $days = [$scheduler->getScheduleDay()];
            }

            foreach ($days as $key => $day) {
                $days[$key] = $frequency.$day;
            }

            $rule->setByDay($days);
        } catch (InvalidArgument|InvalidRRule) {
            throw new InvalidSchedulerException();
        }

        return $rule;
    }
}
