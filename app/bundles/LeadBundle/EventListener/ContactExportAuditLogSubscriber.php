<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Event\ContactExportEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ContactExportAuditLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuditLogModel $auditLogModel,
        private IpLookupHelper $ipLookupHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::POST_CONTACT_EXPORT  => 'onContactExport',
        ];
    }

    public function onContactExport(ContactExportEvent $event): void
    {
        $this->auditLogModel->writeToLog(
            [
                'bundle'    => 'lead',
                'object'    => $event->getObject(),
                'objectId'  => 0,
                'action'    => 'create',
                'details'   => $event->getArgs(),
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ]
        );
    }
}
