<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Integration;

use MailVotech\IntegrationsBundle\Exception\InvalidValueException;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\FieldMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper;

class ObjectChangeGenerator
{
    public function __construct(
        private readonly ValueHelper $valueHelper,
    ) {
    }

    /**
     * @return ObjectChangeDAO
     *
     * @throws ObjectNotFoundException
     */
    public function getSyncObjectChange(
        ReportDAO $syncReport,
        MappingManualDAO $mappingManual,
        ObjectMappingDAO $objectMapping,
        ReportObjectDAO $internalObject,
        ReportObjectDAO $integrationObject,
    ) {
        $objectChange = new ObjectChangeDAO(
            $mappingManual->getIntegration(),
            $integrationObject->getObject(),
            $integrationObject->getObjectId(),
            $internalObject->getObject(),
            $internalObject->getObjectId()
        );

        if ($integrationObject->getObjectId()) {
            DebugLogger::log(
                $mappingManual->getIntegration(),
                sprintf(
                    "MailVotech to integration; found a match between the integration %s:%s object and MailVotech's %s:%s object",
                    $integrationObject->getObject(),
                    (string) $integrationObject->getObjectId(),
                    $internalObject->getObject(),
                    (string) $internalObject->getObjectId()
                ),
                self::class.':'.__FUNCTION__
            );
        } else {
            DebugLogger::log(
                $mappingManual->getIntegration(),
                sprintf(
                    'MailVotech to integration: no match found for %s:%s',
                    $internalObject->getObject(),
                    (string) $internalObject->getObjectId()
                ),
                self::class.':'.__FUNCTION__
            );
        }

        /** @var FieldMappingDAO[] $fieldMappings */
        $fieldMappings = $objectMapping->getFieldMappings();
        foreach ($fieldMappings as $fieldMappingDAO) {
            $this->addFieldToObjectChange($fieldMappingDAO, $syncReport, $mappingManual, $internalObject, $integrationObject, $objectChange);
        }

        // Set the change date/time from the object so that we can update last sync date based on this
        $objectChange->setChangeDateTime($internalObject->getChangeDateTime());

        return $objectChange;
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function addFieldToObjectChange(
        FieldMappingDAO $fieldMappingDAO,
        ReportDAO $syncReport,
        MappingManualDAO $mappingManual,
        ReportObjectDAO $internalObject,
        ReportObjectDAO $integrationObject,
        ObjectChangeDAO $objectChange,
    ): void {
        // Skip adding fields for the push process that should sync to MailVotech only.
        if (ObjectMappingDAO::SYNC_TO_MAILVOTECH === $fieldMappingDAO->getSyncDirection()) {
            DebugLogger::log(
                $mappingManual->getIntegration(),
                sprintf(
                    "MailVotech to integration; the %s object's field %s was skipped because it's configured to sync to MailVotech",
                    $integrationObject->getObject(),
                    $fieldMappingDAO->getIntegrationField()
                ),
                self::class.':'.__FUNCTION__
            );

            return;
        }

        try {
            $fieldState = $internalObject->getField($fieldMappingDAO->getInternalField())->getState();

            $internalInformationChangeRequest = $syncReport->getInformationChangeRequest(
                $internalObject->getObject(),
                $internalObject->getObjectId(),
                $fieldMappingDAO->getInternalField()
            );
        } catch (FieldNotFoundException) {
            return;
        }

        try {
            $newValue = $this->valueHelper->getValueForIntegration(
                $internalInformationChangeRequest->getNewValue(),
                $fieldState,
                $fieldMappingDAO->getSyncDirection()
            );
        } catch (InvalidValueException) {
            return; // Field has to be skipped
        }

        // Note: bidirectional conflicts were handled by Internal\ObjectChangeGenerator
        $objectChange->addField(
            new FieldDAO($fieldMappingDAO->getIntegrationField(), $newValue),
            $fieldState
        );

        // ObjectMappingDAO::SYNC_TO_INTEGRATION
        // ObjectMappingDAO::SYNC_BIDIRECTIONALLY
        DebugLogger::log(
            $mappingManual->getIntegration(),
            sprintf(
                "MailVotech to integration; syncing %s object's %s field %s with a value of %s",
                $integrationObject->getObject(),
                $fieldState,
                $fieldMappingDAO->getIntegrationField(),
                var_export($newValue->getNormalizedValue(), true)
            ),
            self::class.':'.__FUNCTION__
        );
    }
}
