<?php

namespace MailVotech\ReportBundle\Scheduler\EventListener;

use MailVotech\ReportBundle\Event\ReportEvent;
use MailVotech\ReportBundle\ReportEvents;
use MailVotech\ReportBundle\Scheduler\Model\SchedulerPlanner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ReportSchedulerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SchedulerPlanner $schedulerPlanner,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ReportEvents::REPORT_POST_SAVE => ['onReportSave', 0]];
    }

    public function onReportSave(ReportEvent $event): void
    {
        $report = $event->getReport();

        $this->schedulerPlanner->computeScheduler($report);
    }
}
