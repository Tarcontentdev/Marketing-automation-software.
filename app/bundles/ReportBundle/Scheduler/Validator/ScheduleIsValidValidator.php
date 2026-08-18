<?php

namespace MailVotech\ReportBundle\Scheduler\Validator;

use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Scheduler\Builder\SchedulerBuilder;
use MailVotech\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use MailVotech\ReportBundle\Scheduler\Exception\NotSupportedScheduleTypeException;
use MailVotech\ReportBundle\Scheduler\Exception\ScheduleNotValidException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ScheduleIsValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly SchedulerBuilder $schedulerBuilder,
    ) {
    }

    /**
     * @param Report $report
     */
    public function validate($report, Constraint $constraint): void
    {
        if (!$report->isScheduled()) {
            $report->setAsNotScheduled();

            return;
        }

        if (null === $report->getToAddress()) {
            $this->context->buildViolation('mailvotech.report.schedule.to_address_required')
                ->atPath('toAddress')
                ->addViolation();
        }

        if ($report->isScheduledDaily()) {
            $report->ensureIsDailyScheduled();
            $this->buildScheduler($report);

            return;
        }
        if ($report->isScheduledWeekly()) {
            try {
                $report->ensureIsWeeklyScheduled();
                $this->buildScheduler($report);

                return;
            } catch (ScheduleNotValidException) {
                $this->addReportScheduleNotValidViolation();
            }
        }
        if ($report->isScheduledMonthly()) {
            try {
                $report->ensureIsMonthlyScheduled();
                $this->buildScheduler($report);

                return;
            } catch (ScheduleNotValidException) {
                $this->addReportScheduleNotValidViolation();
            }
        }
    }

    private function addReportScheduleNotValidViolation(): void
    {
        $this->context->buildViolation('mailvotech.report.schedule.notValid')
            ->atPath('isScheduled')
            ->addViolation();
    }

    private function buildScheduler(Report $report): void
    {
        try {
            $this->schedulerBuilder->getNextEvent($report);

            return;
        } catch (InvalidSchedulerException) {
            $message = 'mailvotech.report.schedule.notValid';
        } catch (NotSupportedScheduleTypeException) {
            $message = 'mailvotech.report.schedule.notSupportedType';
        }

        $this->context->buildViolation($message)
            ->atPath('isScheduled')
            ->addViolation();
    }
}
