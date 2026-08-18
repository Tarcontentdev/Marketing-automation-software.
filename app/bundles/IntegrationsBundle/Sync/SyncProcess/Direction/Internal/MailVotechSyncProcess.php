<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectSyncSkippedException;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;

class MailVotechSyncProcess
{
    private ?InputOptionsDAO $inputOptionsDAO = null;

    private ?MappingManualDAO $mappingManualDAO = null;

    private ?MailVotechSyncDataExchange $syncDataExchange = null;

    public function __construct(
        private readonly SyncDateHelper $syncDateHelper,
        private readonly ObjectChangeGenerator $objectChangeGenerator,
    ) {
    }

    public function setupSync(InputOptionsDAO $inputOptionsDAO, MappingManualDAO $mappingManualDAO, MailVotechSyncDataExchange $syncDataExchange): void
    {
        $this->inputOptionsDAO  = $inputOptionsDAO;
        $this->mappingManualDAO = $mappingManualDAO;
        $this->syncDataExchange = $syncDataExchange;
    }

    /**
     * @throws ObjectNotFoundException
     */
    public function getSyncReport(int $syncIteration): ReportDAO
    {
        $internalRequestDAO     = new RequestDAO($this->mappingManualDAO->getIntegration(), $syncIteration, $this->inputOptionsDAO);
        $mailvotechObjectTypes      = $internalRequestDAO->getInputOptionsDAO()->getMailVotechObjectIds() ?
            $internalRequestDAO->getInputOptionsDAO()->getMailVotechObjectIds()->getObjectTypes() : [];
        $hasMailVotechObjectIDs = 0 < count($mailvotechObjectTypes);

        $internalObjectsNames = $this->mappingManualDAO->getInternalObjectNames();
        foreach ($internalObjectsNames as $internalObjectName) {
            if ($hasMailVotechObjectIDs) {
                try {
                    $internalRequestDAO->getInputOptionsDAO()->getMailVotechObjectIds()->getObjectIdsFor($internalObjectName);
                } catch (ObjectNotFoundException) {
                    DebugLogger::log(
                        $this->mappingManualDAO->getIntegration(),
                        sprintf(
                            'MailVotech to integration; skipping sync for the %s object because certain object IDs are specified for other object(s)',
                            $internalObjectName
                        ),
                        self::class.':'.__FUNCTION__
                    );
                    continue;
                }
            }

            $internalObjectFields = $this->mappingManualDAO->getInternalObjectFieldsToSyncToIntegration($internalObjectName);
            if (0 === count($internalObjectFields)) {
                // No fields configured for a sync
                DebugLogger::log(
                    $this->mappingManualDAO->getIntegration(),
                    sprintf(
                        'MailVotech to integration; there are no fields for the %s object',
                        $internalObjectName
                    ),
                    self::class.':'.__FUNCTION__
                );

                continue;
            }

            $objectSyncFromDateTime = $this->syncDateHelper->getSyncFromDateTime(MailVotechSyncDataExchange::NAME, $internalObjectName);
            $objectSyncToDateTime   = $this->syncDateHelper->getSyncToDateTime();
            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf(
                    'MailVotech to integration; syncing from %s to %s for the %s object with %d fields',
                    $objectSyncFromDateTime->format('Y-m-d H:i:s'),
                    $objectSyncToDateTime->format('Y-m-d H:i:s'),
                    $internalObjectName,
                    count($internalObjectFields)
                ),
                self::class.':'.__FUNCTION__
            );

            $internalRequestObject  = new RequestObjectDAO($internalObjectName, $objectSyncFromDateTime, $objectSyncToDateTime);
            foreach ($internalObjectFields as $internalObjectField) {
                $internalRequestObject->addField($internalObjectField);
            }

            // Set required fields for easy access; mainly for MailVotech
            $internalRequestObject->setRequiredFields($this->mappingManualDAO->getInternalObjectRequiredFieldNames($internalObjectName));
            $internalRequestDAO->addObject($internalRequestObject);
        }

        return $internalRequestDAO->shouldSync()
            ? $this->syncDataExchange->getSyncReport($internalRequestDAO)
            :
            new ReportDAO(MailVotechSyncDataExchange::NAME);
    }

    /**
     * @throws ObjectNotFoundException
     * @throws ObjectNotSupportedException
     */
    public function getSyncOrder(ReportDAO $syncReport): OrderDAO
    {
        $orderDAO = new OrderDAO($this->syncDateHelper->getSyncDateTime(), $this->inputOptionsDAO->isFirstTimeSync(), $this->mappingManualDAO->getIntegration(), $this->inputOptionsDAO->getOptions());

        $integrationObjectsNames = $this->mappingManualDAO->getIntegrationObjectNames();

        foreach ($integrationObjectsNames as $integrationObjectName) {
            $integrationObjects         = $syncReport->getObjects($integrationObjectName);
            $mappedInternalObjectsNames = $this->mappingManualDAO->getMappedInternalObjectsNames($integrationObjectName);

            DebugLogger::log(
                $this->mappingManualDAO->getIntegration(),
                sprintf(
                    'Integration to MailVotech; found %d objects for the %s object mapped to the %s MailVotech object(s)',
                    count($integrationObjects),
                    $integrationObjectName,
                    implode(', ', $mappedInternalObjectsNames)
                ),
                self::class.':'.__FUNCTION__
            );

            foreach ($mappedInternalObjectsNames as $mappedInternalObjectName) {
                $objectMapping = $this->mappingManualDAO->getObjectMapping($mappedInternalObjectName, $integrationObjectName);
                foreach ($integrationObjects as $integrationObject) {
                    try {
                        $internalObject = $this->syncDataExchange->getConflictedInternalObject(
                            $this->mappingManualDAO,
                            $mappedInternalObjectName,
                            $integrationObject
                        );
                        $objectChange   = $this->objectChangeGenerator->getSyncObjectChange(
                            $syncReport,
                            $this->mappingManualDAO,
                            $objectMapping,
                            $internalObject,
                            $integrationObject
                        );

                        if ($objectChange->shouldSync()) {
                            $orderDAO->addObjectChange($objectChange);
                        }
                    } catch (ObjectDeletedException) {
                        DebugLogger::log(
                            $this->mappingManualDAO->getIntegration(),
                            sprintf(
                                'Integration to MailVotech; the %s object with ID %s is marked deleted and thus not synced',
                                $integrationObject->getObject(),
                                $integrationObject->getObjectId()
                            ),
                            self::class.':'.__FUNCTION__
                        );
                    } catch (ObjectSyncSkippedException $exception) {
                        DebugLogger::log(
                            $this->mappingManualDAO->getIntegration(),
                            sprintf(
                                'Integration to MailVotech; the %s object with ID %s is skipped and thus not synced with message: '.$exception->getMessage(),
                                $integrationObject->getObject(),
                                $integrationObject->getObjectId()
                            ),
                            self::class.':'.__FUNCTION__
                        );
                    }
                }
            }
        }

        return $orderDAO;
    }
}
