<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\EventListener;

use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Entity\Scheduler;
use MailVotech\ReportBundle\Event\ReportScheduleSendEvent;
use MailVotech\ReportBundle\EventListener\SchedulerSubscriber;
use MailVotech\ReportBundle\Scheduler\Model\SendSchedule;

final class SchedulerSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testNoEmailsProvided(): void
    {
        $sendScheduleMock = $this->createMock(SendSchedule::class);

        $schedulerSubscriber = new SchedulerSubscriber($sendScheduleMock);

        $report                  = new Report();
        $date                    = new \DateTime();
        $scheduler               = new Scheduler($report, $date);
        $file                    = 'path-to-a-file';
        $reportScheduleSendEvent = new ReportScheduleSendEvent($scheduler, $file);

        $sendScheduleMock->expects($this->once())
            ->method('send')
            ->with($scheduler, $file);

        $schedulerSubscriber->onScheduleSend($reportScheduleSendEvent);
    }
}
