<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncDataExchange;

use MailVotech\IntegrationsBundle\Entity\FieldChangeRepository;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MailVotechSyncDataExchangeTest extends TestCase
{
    /**
     * @var MockObject&FieldChangeRepository
     */
    private MockObject $fieldChangeRepository;

    /**
     * @var MockObject&FieldHelper
     */
    private MockObject $fieldHelper;

    /**
     * @var MockObject&MappingHelper
     */
    private MockObject $mappingHelper;

    /**
     * @var MockObject&FullObjectReportBuilder
     */
    private MockObject $fullObjectReportBuilder;

    /**
     * @var MockObject&PartialObjectReportBuilder
     */
    private MockObject $partialObjectReportBuilder;

    private MailVotechSyncDataExchange $mailvotechSyncDataExchange;

    protected function setUp(): void
    {
        $this->fieldChangeRepository      = $this->createMock(FieldChangeRepository::class);
        $this->fieldHelper                = $this->createMock(FieldHelper::class);
        $this->mappingHelper              = $this->createMock(MappingHelper::class);
        $this->fullObjectReportBuilder    = $this->createMock(FullObjectReportBuilder::class);
        $this->partialObjectReportBuilder = $this->createMock(PartialObjectReportBuilder::class);

        $this->mailvotechSyncDataExchange = new MailVotechSyncDataExchange(
            $this->fieldChangeRepository,
            $this->fieldHelper,
            $this->mappingHelper,
            $this->fullObjectReportBuilder,
            $this->partialObjectReportBuilder,
            $this->createStub(OrderExecutioner::class),
            $this->createStub(SyncDateHelper::class)
        );
    }

    public function testFirstTimeSyncUsesFullObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration'     => 'foobar',
                'first-time-sync' => true,
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->mailvotechSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testSyncingSpecificMailVotechIdsUseFullObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration'      => 'foobar',
                'mailvotech-object-id' => [1, 2, 3],
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->mailvotechSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testUseOfPartialObjectBuilder(): void
    {
        $inputOptionsDAO = new InputOptionsDAO(
            [
                'integration' => 'foobar',
            ]
        );

        $requestDAO = new RequestDAO('foobar', 1, $inputOptionsDAO);

        $this->fullObjectReportBuilder->expects($this->never())
            ->method('buildReport')
            ->with($requestDAO);

        $this->partialObjectReportBuilder->expects($this->once())
            ->method('buildReport')
            ->with($requestDAO);

        $this->mailvotechSyncDataExchange->getSyncReport($requestDAO);
    }

    public function testGetConflictedInternalObjectWithNoObjectId(): void
    {
        $mappingManualDao     = new MappingManualDAO('IntegrationA');
        $integrationObjectDao = new ObjectDAO('Lead', 'some-SF-ID');

        $this->mappingHelper->expects($this->once())
            ->method('findMailVotechObject')
            ->with($mappingManualDao, 'lead', $integrationObjectDao)
            ->willReturn(new ObjectDAO('lead', null));

        // No need to make the DB query when ID is null.
        $this->fieldChangeRepository->expects($this->never())
            ->method('findChangesForObject');

        $internalObjectDao = $this->mailvotechSyncDataExchange->getConflictedInternalObject($mappingManualDao, 'lead', $integrationObjectDao);

        $this->assertSame('lead', $internalObjectDao->getObject());
        $this->assertNull($internalObjectDao->getObjectId());
    }

    public function testGetConflictedInternalObjectWithObjectId(): void
    {
        $mappingManualDao     = new MappingManualDAO('IntegrationA');
        $integrationObjectDao = new ObjectDAO('Lead', 'some-SF-ID');
        $fieldChange          = [
            'modified_at'  => '2020-08-25 17:20:00',
            'column_type'  => 'text',
            'column_value' => 'some-field-value',
            'column_name'  => 'some-field-name',
        ];

        $this->mappingHelper->expects($this->once())
            ->method('findMailVotechObject')
            ->with($mappingManualDao, 'lead', $integrationObjectDao)
            ->willReturn(new ObjectDAO('lead', 123));

        $this->mappingHelper->method('getMailVotechEntityClassName')
            ->with('lead')
            ->willReturn(Lead::class);

        $this->fieldHelper->method('getFieldChangeObject')
            ->with($fieldChange)
            ->willReturn(new FieldDAO('some-field-name', new NormalizedValueDAO('type', 'some-field-value')));

        $this->fieldChangeRepository->expects($this->once())
            ->method('findChangesForObject')
            ->with('IntegrationA', Lead::class, 123)
            ->willReturn([$fieldChange]);

        $internalObjectDao = $this->mailvotechSyncDataExchange->getConflictedInternalObject($mappingManualDao, 'lead', $integrationObjectDao);

        $this->assertSame('lead', $internalObjectDao->getObject());
        $this->assertSame(123, $internalObjectDao->getObjectId());
        $this->assertCount(1, $internalObjectDao->getFields());
    }
}
