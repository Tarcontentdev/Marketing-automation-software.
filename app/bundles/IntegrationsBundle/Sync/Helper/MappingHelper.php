<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Helper;

use MailVotech\IntegrationsBundle\Entity\ObjectMapping;
use MailVotech\IntegrationsBundle\Entity\ObjectMappingRepository;
use MailVotech\IntegrationsBundle\Event\InternalObjectFindEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\RemappedObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MappingHelper
{
    public function __construct(
        private readonly FieldsWithUniqueIdentifier $fieldsWithUniqueIdentifier,
        private readonly ObjectMappingRepository $objectMappingRepository,
        private readonly ObjectProvider $objectProvider,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws ObjectDeletedException
     * @throws ObjectNotFoundException
     * @throws ObjectNotSupportedException
     */
    public function findMailVotechObject(MappingManualDAO $mappingManualDAO, string $internalObjectName, ObjectDAO $integrationObjectDAO): ObjectDAO
    {
        // Check if this contact is already tracked
        if ($internalObject = $this->objectMappingRepository->getInternalObject(
            $mappingManualDAO->getIntegration(),
            $integrationObjectDAO->getObject(),
            $integrationObjectDAO->getObjectId(),
            $internalObjectName
        )) {
            if ($internalObject['is_deleted']) {
                throw new ObjectDeletedException();
            }

            return new ObjectDAO(
                $internalObjectName,
                $internalObject['internal_object_id'],
                new \DateTime($internalObject['last_sync_date'], new \DateTimeZone('UTC'))
            );
        }

        // We don't know who this is so search MailVotech
        $uniqueIdentifierFields = $this->fieldsWithUniqueIdentifier->getFieldsWithUniqueIdentifier(['object' => $internalObjectName]);
        $identifiers            = [];

        foreach ($uniqueIdentifierFields as $field => $fieldLabel) {
            try {
                $integrationField = $mappingManualDAO->getIntegrationMappedField($integrationObjectDAO->getObject(), $internalObjectName, $field);
                $integrationValue = $integrationObjectDAO->getField($integrationField);
                $identifiers[$field] = $integrationValue->getValue()->getNormalizedValue();
            } catch (FieldNotFoundException) {
            }
        }

        if ([] === $identifiers) {
            // No fields found to search for contact so return null
            return new ObjectDAO($internalObjectName, null);
        }

        try {
            $event = new InternalObjectFindEvent(
                $this->objectProvider->getObjectByName($internalObjectName)
            );
        } catch (ObjectNotFoundException) {
            // Throw this exception for BC.
            throw new ObjectNotSupportedException(MailVotechSyncDataExchange::NAME, $internalObjectName);
        }

        $event->setFieldValues($identifiers);

        $this->dispatcher->dispatch(
            $event,
            IntegrationEvents::INTEGRATION_FIND_INTERNAL_RECORDS,
        );

        $foundObjects = $event->getFoundObjects();

        if ([] === $foundObjects) {
            // No contacts were found
            return new ObjectDAO($internalObjectName, null);
        }

        // Match found!
        $objectId = $foundObjects[0]['id'];

        // Let's store the relationship since we know it
        $objectMapping = new ObjectMapping();
        $objectMapping->setLastSyncDate($integrationObjectDAO->getChangeDateTime())
            ->setIntegration($mappingManualDAO->getIntegration())
            ->setIntegrationObjectName($integrationObjectDAO->getObject())
            ->setIntegrationObjectId($integrationObjectDAO->getObjectId())
            ->setInternalObjectName($internalObjectName)
            ->setInternalObjectId($objectId);
        $this->saveObjectMapping($objectMapping);

        return new ObjectDAO($internalObjectName, $objectId);
    }

    /**
     * Returns corresponding MailVotech entity class name for the given MailVotech object.
     *
     * @throws ObjectNotSupportedException
     */
    public function getMailVotechEntityClassName(string $internalObject): string
    {
        try {
            return $this->objectProvider->getObjectByName($internalObject)->getEntityName();
        } catch (ObjectNotFoundException) {
            // Throw this exception instead to keep BC.
            throw new ObjectNotSupportedException(MailVotechSyncDataExchange::NAME, $internalObject);
        }
    }

    /**
     * @throws ObjectDeletedException
     */
    public function findIntegrationObject(string $integration, string $integrationObjectName, ObjectDAO $internalObjectDAO): ObjectDAO
    {
        if ($integrationObject = $this->objectMappingRepository->getIntegrationObject(
            $integration,
            $internalObjectDAO->getObject(),
            $internalObjectDAO->getObjectId(),
            $integrationObjectName
        )) {
            if ($integrationObject['is_deleted']) {
                throw new ObjectDeletedException();
            }

            return new ObjectDAO(
                $integrationObjectName,
                $integrationObject['integration_object_id'],
                new \DateTime($integrationObject['last_sync_date'], new \DateTimeZone('UTC'))
            );
        }

        return new ObjectDAO($integrationObjectName, null);
    }

    /**
     * @param ObjectMapping[] $mappings
     */
    public function saveObjectMappings(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            $this->saveObjectMapping($mapping);
        }
    }

    public function updateObjectMappings(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            try {
                $this->updateObjectMapping($mapping);
            } catch (ObjectNotFoundException) {
                continue;
            }
        }
    }

    /**
     * @param RemappedObjectDAO[] $mappings
     */
    public function remapIntegrationObjects(array $mappings): void
    {
        foreach ($mappings as $mapping) {
            $this->objectMappingRepository->updateIntegrationObject(
                $mapping->getIntegration(),
                $mapping->getOldObjectName(),
                $mapping->getOldObjectId(),
                $mapping->getNewObjectName(),
                $mapping->getNewObjectId()
            );
        }
    }

    /**
     * @param ObjectChangeDAO[] $objects
     */
    public function markAsDeleted(array $objects): void
    {
        foreach ($objects as $object) {
            $this->objectMappingRepository->markAsDeleted($object->getIntegration(), $object->getObject(), $object->getObjectId());
        }
    }

    private function saveObjectMapping(ObjectMapping $objectMapping): void
    {
        $this->objectMappingRepository->saveEntity($objectMapping);
        $this->objectMappingRepository->detachEntity($objectMapping);
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function updateObjectMapping(UpdatedObjectMappingDAO $updatedObjectMappingDAO): void
    {
        /** @var ObjectMapping $objectMapping */
        $objectMapping = $this->objectMappingRepository->findOneBy(
            [
                'integration'           => $updatedObjectMappingDAO->getIntegration(),
                'integrationObjectName' => $updatedObjectMappingDAO->getIntegrationObjectName(),
                'integrationObjectId'   => $updatedObjectMappingDAO->getIntegrationObjectId(),
            ]
        );

        if (!$objectMapping) {
            throw new ObjectNotFoundException($updatedObjectMappingDAO->getIntegrationObjectName().':'.$updatedObjectMappingDAO->getIntegrationObjectId());
        }

        $objectMapping->setLastSyncDate($updatedObjectMappingDAO->getObjectModifiedDate());

        $this->saveObjectMapping($objectMapping);

        // Make the ObjectMapping available to the IntegrationEvents::INTEGRATION_BATCH_SYNC_COMPLETED_* events
        $updatedObjectMappingDAO->setObjectMapping($objectMapping);
    }
}
