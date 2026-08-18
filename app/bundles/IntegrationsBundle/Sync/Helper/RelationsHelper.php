<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Helper;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\RelationDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\InternalIdNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;

class RelationsHelper
{
    /**
     * @var ObjectDAO[]
     */
    private array $objectsToSynchronize = [];

    public function __construct(
        private readonly MappingHelper $mappingHelper,
    ) {
    }

    public function processRelations(MappingManualDAO $mappingManualDao, ReportDAO $syncReport): void
    {
        $this->objectsToSynchronize = [];
        foreach ($syncReport->getRelations() as $relationObject) {
            if (0 < $relationObject->getRelObjectInternalId()) {
                continue;
            }

            $this->processRelation($mappingManualDao, $syncReport, $relationObject);
        }
    }

    public function getObjectsToSynchronize(): array
    {
        return $this->objectsToSynchronize;
    }

    /**
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     */
    private function processRelation(MappingManualDAO $mappingManualDao, ReportDAO $syncReport, RelationDAO $relationObject): void
    {
        $relObjectDao = new ObjectDAO($relationObject->getRelObjectName(), $relationObject->getRelObjectIntegrationId());

        try {
            $internalObjectName = $this->getInternalObjectName($mappingManualDao, $relationObject->getRelObjectName());
            $internalObjectId   = $this->getInternalObjectId($mappingManualDao, $relationObject, $relObjectDao);
            $this->addObjectInternalId($internalObjectId, $internalObjectName, $relationObject, $syncReport);
        } catch (ObjectNotFoundException) {
            return; // We are not mapping this object
        } catch (InternalIdNotFoundException) {
            $this->objectsToSynchronize[] = $relObjectDao;
        }
    }

    /**
     * @throws InternalIdNotFoundException
     */
    private function getInternalObjectId(MappingManualDAO $mappingManualDao, RelationDAO $relationObject, ObjectDAO $relObjectDao): int
    {
        $relObject        = $this->findInternalObject($mappingManualDao, $relationObject->getRelObjectName(), $relObjectDao);
        $internalObjectId = (int) $relObject->getObjectId();

        if ($internalObjectId) {
            return $internalObjectId;
        }

        throw new InternalIdNotFoundException($relationObject->getRelObjectName());
    }

    /**
     * @throws ObjectNotFoundException
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     */
    private function findInternalObject(MappingManualDAO $mappingManualDao, string $relObjectName, ObjectDAO $objectDao): ObjectDAO
    {
        $internalObjectsName = $this->getInternalObjectName($mappingManualDao, $relObjectName);

        return $this->mappingHelper->findMailVotechObject($mappingManualDao, $internalObjectsName, $objectDao);
    }

    /**
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException
     */
    private function addObjectInternalId(int $relObjectId, string $relInternalType, RelationDAO $relationObject, ReportDAO $syncReport): void
    {
        $relationObject->setRelObjectInternalId($relObjectId);
        $objectDAO      = $syncReport->getObject($relationObject->getObjectName(), $relationObject->getObjectIntegrationId());
        $referenceValue = $objectDAO->getField($relationObject->getRelFieldName())->getValue()->getNormalizedValue();
        $referenceValue->setType($relInternalType);
        $referenceValue->setValue($relObjectId);
    }

    /**
     * @return mixed
     *
     * @throws ObjectNotFoundException
     */
    private function getInternalObjectName(MappingManualDAO $mappingManualDao, string $relObjectName)
    {
        $internalObjectsNames = $mappingManualDao->getMappedInternalObjectsNames($relObjectName);

        return $internalObjectsNames[0];
    }
}
