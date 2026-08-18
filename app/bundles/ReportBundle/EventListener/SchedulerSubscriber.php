<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\EventListener;

use MailVotech\ReportBundle\Event\ReportScheduleSendEvent;
use MailVotech\ReportBundle\ReportEvents;
use MailVotech\ReportBundle\Scheduler\Model\SendSchedule;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SchedulerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SendSchedule $sendSchedule,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvents::REPORT_SCHEDULE_SEND => ['onScheduleSend', 0],
        ];
    }

    public function onScheduleSend(ReportScheduleSendEvent $event): void
    {
        $scheduler = $event->getScheduler();
        $file      = $event->getFile();

        $this->sendSchedule->send($scheduler, $file);
    }
}
