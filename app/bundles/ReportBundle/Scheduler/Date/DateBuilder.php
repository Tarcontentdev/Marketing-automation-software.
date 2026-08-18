<?php

namespace MailVotech\ReportBundle\Scheduler\Date;

use MailVotech\ReportBundle\Scheduler\Builder\SchedulerBuilder;
use MailVotech\ReportBundle\Scheduler\Entity\SchedulerEntity;
use MailVotech\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use MailVotech\ReportBundle\Scheduler\Exception\NoScheduleException;
use MailVotech\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use MailVotech\ReportBundle\Scheduler\SchedulerInterface;

class DateBuilder
{
    public function __construct(
        private readonly SchedulerBuilder $schedulerBuilder,
    ) {
    }

    /**
     * @param bool   $isScheduled
     * @param string $scheduleUnit
     * @param string $scheduleDay
     * @param string $scheduleMonthFrequency
     */
    public function getPreviewDays($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency): array
    {
        $entity = new SchedulerEntity($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency);
        $count  = $entity->isScheduledNow() ? 1 : 10;

        try {
            $recurrences = $this->schedulerBuilder->getNextEvents($entity, $count);
        } catch (InvalidSchedulerException|NotSupportedScheduleTypeException) {
            return [];
        }

        $dates = [];
        foreach ($recurrences as $recurrence) {
            $dates[] = $recurrence->getStart();
        }

        return $dates;
    }

    /**
     * @return \DateTimeInterface
     *
     * @throws NoScheduleException
     */
    public function getNextEvent(SchedulerInterface $scheduler)
    {
        try {
            $recurrences = $this->schedulerBuilder->getNextEvent($scheduler);
        } catch (InvalidSchedulerException|NotSupportedScheduleTypeException) {
            throw new NoScheduleException();
        }

        if (empty($recurrences[0])) {
            throw new NoScheduleException();
        }

        return $recurrences[0]->getStart();
    }
}
