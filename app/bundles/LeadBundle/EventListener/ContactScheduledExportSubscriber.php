<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\LeadBundle\Event\ContactExportSchedulerEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\ContactExportSchedulerModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContactScheduledExportSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ContactExportSchedulerModel $contactExportSchedulerModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::CONTACT_EXPORT_PREPARE_FILE    => 'onContactExportPrepareFile',
            LeadEvents::CONTACT_EXPORT_SEND_EMAIL      => 'onContactExportSendEmail',
            LeadEvents::POST_CONTACT_EXPORT_SEND_EMAIL => 'onContactExportEmailSent',
        ];
    }

    public function onContactExportPrepareFile(ContactExportSchedulerEvent $event): void
    {
        $contactExportScheduler = $event->getContactExportScheduler();
        $filePath               = $this->contactExportSchedulerModel->processAndGetExportFilePath($contactExportScheduler);
        $event->setFilePath($filePath);
    }

    public function onContactExportSendEmail(ContactExportSchedulerEvent $event): void
    {
        $contactExportScheduler = $event->getContactExportScheduler();
        $this->contactExportSchedulerModel->sendEmail($contactExportScheduler, $event->getFilePath());
    }

    public function onContactExportEmailSent(ContactExportSchedulerEvent $event): void
    {
        $contactExportScheduler = $event->getContactExportScheduler();
        $this->contactExportSchedulerModel->deleteEntity($contactExportScheduler);
    }
}
