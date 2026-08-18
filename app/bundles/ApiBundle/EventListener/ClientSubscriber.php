<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\EventListener;

use MailVotech\ApiBundle\ApiEvents;
use MailVotech\ApiBundle\Event as Events;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ClientSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IpLookupHelper $ipLookupHelper,
        private AuditLogModel $auditLogModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApiEvents::CLIENT_POST_SAVE   => ['onClientPostSave', 0],
            ApiEvents::CLIENT_POST_DELETE => ['onClientDelete', 0],
        ];
    }

    /**
     * Add a client change entry to the audit log.
     */
    public function onClientPostSave(Events\ClientEvent $event): void
    {
        $client = $event->getClient();
        if (!$details = $event->getChanges()) {
            return;
        }

        $log = [
            'bundle'    => 'api',
            'object'    => 'client',
            'objectId'  => $client->getId(),
            'action'    => ($event->isNew()) ? 'create' : 'update',
            'details'   => $details,
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    /**
     * Add a role delete entry to the audit log.
     */
    public function onClientDelete(Events\ClientEvent $event): void
    {
        $client = $event->getClient();
        $log    = [
            'bundle'    => 'api',
            'object'    => 'client',
            'objectId'  => $client->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $client->getName()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }
}
