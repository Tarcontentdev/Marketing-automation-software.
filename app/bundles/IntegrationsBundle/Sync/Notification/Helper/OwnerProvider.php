<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Helper;

use MailVotech\IntegrationsBundle\Event\InternalObjectOwnerEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OwnerProvider
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ObjectProvider $objectProvider,
    ) {
    }

    /**
     * @param int[] $objectIds
     *
     * @throws ObjectNotSupportedException
     */
    public function getOwnersForObjectIds(string $objectName, array $objectIds): array
    {
        if ([] === $objectIds) {
            return [];
        }

        try {
            $object = $this->objectProvider->getObjectByName($objectName);
        } catch (ObjectNotFoundException) {
            // Throw this exception for BC.
            throw new ObjectNotSupportedException(MailVotechSyncDataExchange::NAME, $objectName);
        }

        $event = new InternalObjectOwnerEvent($object, $objectIds);

        $this->dispatcher->dispatch($event, IntegrationEvents::INTEGRATION_FIND_OWNER_IDS);

        return $event->getOwners();
    }
}
