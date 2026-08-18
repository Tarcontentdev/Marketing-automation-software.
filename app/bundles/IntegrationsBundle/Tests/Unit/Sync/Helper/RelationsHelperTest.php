<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\Helper;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\RelationsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\RelationDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\ReferenceValueDAO;
use MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\RelationsHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use PHPUnit\Framework\TestCase;

final class RelationsHelperTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&MappingHelper
     */
    private \PHPUnit\Framework\MockObject\MockObject $mappingHelper;

    private RelationsHelper $relationsHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ReportDAO
     */
    private \PHPUnit\Framework\MockObject\MockObject $syncReport;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&MappingManualDAO
     */
    private \PHPUnit\Framework\MockObject\MockObject $mappingManual;

    protected function setUp(): void
    {
        $this->mappingHelper   = $this->createMock(MappingHelper::class);
        $this->relationsHelper = new RelationsHelper($this->mappingHelper);
        $this->syncReport      = $this->createMock(ReportDAO::class);
        $this->mappingManual   = $this->createMock(MappingManualDAO::class);
    }

    public function testProcessRelationsWithUnsychronisedObjects(): void
    {
        $integrationObjectId    = 'IntegrationId-123';
        $integrationRelObjectId = 'IntegrationId-456';
        $relObjectName          = 'Account';

        $relationObject = new RelationDAO(
            'Contact',
            'AccountId',
            $relObjectName,
            $integrationObjectId,
            $integrationRelObjectId
        );

        $relationsObject = new RelationsDAO();
        $relationsObject->addRelation($relationObject);

        $this->syncReport->expects($this->once())
            ->method('getRelations')
            ->willReturn($relationsObject);

        $this->mappingManual
            ->method('getMappedInternalObjectsNames')
            ->willReturn(['company']);

        $internalObject = new ObjectDAO('company', null);

        $this->mappingHelper->expects($this->once())
            ->method('findMailVotechObject')
            ->willReturn($internalObject);

        $this->relationsHelper->processRelations($this->mappingManual, $this->syncReport);

        $objectsToSynchronize = $this->relationsHelper->getObjectsToSynchronize();

        $this->assertCount(1, $objectsToSynchronize);

        $this->assertEquals($objectsToSynchronize[0]->getObjectId(), $integrationRelObjectId);
        $this->assertEquals($objectsToSynchronize[0]->getObject(), $relObjectName);
    }

    public function testProcessRelationsWithSychronisedObjects(): void
    {
        $integrationObjectId    = 'IntegrationId-123';
        $integrationRelObjectId = 'IntegrationId-456';
        $internalRelObjectId    = 13;
        $relObjectName          = 'Account';
        $relFieldName           = 'AccountId';

        $referenceVlaue  = new ReferenceValueDAO();
        $normalizedValue = new NormalizedValueDAO(NormalizedValueDAO::REFERENCE_TYPE, $integrationRelObjectId, $referenceVlaue);

        $fieldDao  = new FieldDAO('AccountId', $normalizedValue);
        $objectDao = new ObjectDAO('Contact', 1);
        $objectDao->addField($fieldDao);

        $relationObject = new RelationDAO(
            'Contact',
            $relFieldName,
            $relObjectName,
            $integrationObjectId,
            $integrationRelObjectId
        );

        $relationsObject = new RelationsDAO();
        $relationsObject->addRelation($relationObject);

        $this->syncReport->expects($this->once())
            ->method('getRelations')
            ->willReturn($relationsObject);

        $this->syncReport->expects($this->once())
            ->method('getObject')
            ->willReturn($objectDao);

        $this->mappingManual
            ->method('getMappedInternalObjectsNames')
            ->willReturn(['company']);

        $internalObject = new ObjectDAO(MailVotechSyncDataExchange::OBJECT_COMPANY, $internalRelObjectId);

        $this->mappingHelper->expects($this->once())
            ->method('findMailVotechObject')
            ->willReturn($internalObject);

        $this->relationsHelper->processRelations($this->mappingManual, $this->syncReport);

        $objectsToSynchronize = $this->relationsHelper->getObjectsToSynchronize();

        $this->assertCount(0, $objectsToSynchronize);
        $this->assertEquals($internalRelObjectId, $objectDao->getField($relFieldName)->getValue()->getNormalizedValue()->getValue());
        $this->assertEquals(MailVotechSyncDataExchange::OBJECT_COMPANY, $objectDao->getField($relFieldName)->getValue()->getNormalizedValue()->getType());
    }
}
