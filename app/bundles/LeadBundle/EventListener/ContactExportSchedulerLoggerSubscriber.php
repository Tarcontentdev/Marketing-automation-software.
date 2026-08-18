<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use MailVotech\LeadBundle\Event\ContactExportSchedulerEvent;
use MailVotech\LeadBundle\LeadEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContactExportSchedulerLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::POST_CONTACT_EXPORT_SCHEDULED  => 'onContactExportScheduled',
            LeadEvents::POST_CONTACT_EXPORT_SEND_EMAIL => 'onContactExportEmailSent',
        ];
    }

    public function onContactExportScheduled(ContactExportSchedulerEvent $event): void
    {
        $utcScheduledDateTimeStr = $this->getContactExportScheduledDateTimeStr($event->getContactExportScheduler());

        $this->logger->debug(
            'Contact export #ID '.$event->getContactExportScheduler()->getId()
            .' scheduled at '.$utcScheduledDateTimeStr.' UTC'
        );
    }

    public function onContactExportEmailSent(ContactExportSchedulerEvent $event): void
    {
        $utcScheduledDateTimeStr = $this->getContactExportScheduledDateTimeStr($event->getContactExportScheduler());

        $this->logger->debug(
            'Contact export #ID '.$event->getContactExportScheduler()->getId()
            .' scheduled at '.$utcScheduledDateTimeStr.' UTC has been processed at '
            .(new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->format(DateTimeHelper::FORMAT_DB)
            .' UTC'
        );
    }

    private function getContactExportScheduledDateTimeStr(ContactExportScheduler $contactExportScheduler): string
    {
        /** @var \DateTimeImmutable $scheduledDateTime */
        $scheduledDateTime = $contactExportScheduler->getScheduledDateTime();

        return $scheduledDateTime->setTimezone(new \DateTimeZone('UTC'))->format(DateTimeHelper::FORMAT_DB);
    }
}
