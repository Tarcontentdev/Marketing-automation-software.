<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange;

use MailVotech\IntegrationsBundle\Entity\FieldChangeRepository;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectMappingsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder;

class MailVotechSyncDataExchange implements SyncDataExchangeInterface
{
    public const NAME           = 'mailvotech';

    public const OBJECT_CONTACT = 'lead'; // kept as lead for BC

    public const OBJECT_COMPANY = 'company';

    public function __construct(
        private readonly FieldChangeRepository $fieldChangeRepository,
        private readonly FieldHelper $fieldHelper,
        private readonly MappingHelper $mappingHelper,
        private readonly FullObjectReportBuilder $fullObjectReportBuilder,
        private readonly PartialObjectReportBuilder $partialObjectReportBuilder,
        private readonly OrderExecutioner $orderExecutioner,
        private readonly SyncDateHelper $syncDateHelper,
    ) {
    }

    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        if ($requestDAO->isFirstTimeSync() || $requestDAO->getInputOptionsDAO()->getMailVotechObjectIds()) {
            return $this->fullObjectReportBuilder->buildReport($requestDAO);
        }

        return $this->partialObjectReportBuilder->buildReport($requestDAO);
    }

    public function executeSyncOrder(OrderDAO $syncOrderDAO): ObjectMappingsDAO
    {
        return $this->orderExecutioner->execute($syncOrderDAO);
    }

    /**
     * @return ReportObjectDAO
     *
     * @throws ObjectNotFoundException
     * @throws ObjectNotSupportedException
     * @throws ObjectDeletedException
     */
    public function getConflictedInternalObject(MappingManualDAO $mappingManualDAO, string $internalObjectName, ReportObjectDAO $integrationObjectDAO)
    {
        // Check to see if we have a match
        $internalObjectDAO = $this->mappingHelper->findMailVotechObject($mappingManualDAO, $internalObjectName, $integrationObjectDAO);

        if (!$internalObjectDAO->getObjectId()) {
            return new ReportObjectDAO($internalObjectName, null);
        }

        $fieldChanges = $this->fieldChangeRepository->findChangesForObject(
            $mappingManualDAO->getIntegration(),
            $this->mappingHelper->getMailVotechEntityClassName($internalObjectName),
            $internalObjectDAO->getObjectId()
        );

        foreach ($fieldChanges as $fieldChange) {
            $internalObjectDAO->addField(
                $this->fieldHelper->getFieldChangeObject($fieldChange)
            );
        }

        return $internalObjectDAO;
    }

    /**
     * @param ObjectChangeDAO[] $objectChanges
     */
    public function cleanupProcessedObjects(array $objectChanges): void
    {
        foreach ($objectChanges as $changedObjectDAO) {
            try {
                $object   = $this->fieldHelper->getFieldObjectName($changedObjectDAO->getMappedObject());
                $objectId = $changedObjectDAO->getMappedObjectId();

                $this->fieldChangeRepository->deleteEntitiesForObject(
                    (int) $objectId,
                    $object,
                    $changedObjectDAO->getIntegration(),
                    $this->syncDateHelper->getInternalSyncStartDateTime()
                );
            } catch (ObjectNotSupportedException $exception) {
                DebugLogger::log(
                    self::NAME,
                    $exception->getMessage(),
                    self::class.':'.__FUNCTION__
                );
            }
        }
    }
}
