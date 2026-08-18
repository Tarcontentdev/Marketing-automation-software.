<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Scheduler\EventListener;

use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Event\ReportEvent;
use MailVotech\ReportBundle\Scheduler\EventListener\ReportSchedulerSubscriber;
use MailVotech\ReportBundle\Scheduler\Model\SchedulerPlanner;

final class ReportSchedulerSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testOnReportSave(): void
    {
        $report = new Report();
        $event  = new ReportEvent($report);

        $schedulerPlanner = $this->createMock(SchedulerPlanner::class);

        $schedulerPlanner->expects($this->once())
            ->method('computeScheduler')
            ->with($report);

        $reportSchedulerSubscriber = new ReportSchedulerSubscriber($schedulerPlanner);
        $reportSchedulerSubscriber->onReportSave($event);
    }
}
