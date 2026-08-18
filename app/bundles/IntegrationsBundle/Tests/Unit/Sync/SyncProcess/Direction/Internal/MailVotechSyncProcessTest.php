<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncProcess\Direction\Internal;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO as OrderFieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO as ReportFieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectSyncSkippedException;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Company;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MailVotechSyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\ObjectChangeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MailVotechSyncProcessTest extends TestCase
{
    private const INTEGRATION_NAME = 'Test';

    /**
     * @var MockObject&SyncDateHelper
     */
    private MockObject $syncDateHelper;

    /**
     * @var MockObject&ObjectChangeGenerator
     */
    private MockObject $objectChangeGenerator;

    /**
     * @var MockObject&MailVotechSyncDataExchange
     */
    private MockObject $syncDataExchange;

    private InputOptionsDAO $inputOptionsDAO;

    protected function setUp(): void
    {
        $this->syncDateHelper        = $this->createMock(SyncDateHelper::class);
        $this->objectChangeGenerator = $this->createMock(ObjectChangeGenerator::class);
        $this->syncDataExchange      = $this->createMock(MailVotechSyncDataExchange::class);
        $this->inputOptionsDAO       = new InputOptionsDAO(['integration' => self::INTEGRATION_NAME]);
    }

    public function testThatMailVotechGetSyncReportIsCalledBasedOnRequest(): void
    {
        $objectName    = 'Contact';
        $mappingManual = new MappingManualDAO(self::INTEGRATION_NAME);
        $objectMapping = new ObjectMappingDAO(Contact::NAME, $objectName);
        $objectMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $objectMapping->addFieldMapping('firstname', 'first_name');
        $mappingManual->addObjectMapping($objectMapping);

        $fromSyncDateTime = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncFromDateTime')
            ->with(MailVotechSyncDataExchange::NAME, Contact::NAME)
            ->willReturn($fromSyncDateTime);

        $toSyncDateTime   = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncToDateTime')
            ->willReturn($toSyncDateTime);

        // SyncDateExchangeInterface::getSyncReport should sync because an object was added to the report
        $this->syncDataExchange->expects($this->once())
            ->method('getSyncReport')
            ->willReturnCallback(
                function (RequestDAO $requestDAO): ReportDAO {
                    $requestObjects = $requestDAO->getObjects();
                    $this->assertCount(1, $requestObjects);

                    /** @var RequestObjectDAO $requestObject */
                    $requestObject = $requestObjects[0];
                    $this->assertEquals(['email'], $requestObject->getRequiredFields());
                    $this->assertEquals(['email', 'firstname'], $requestObject->getFields());
                    $this->assertEquals(Contact::NAME, $requestObject->getObject());

                    return new ReportDAO(self::INTEGRATION_NAME);
                }
            );

        $this->createMailVotechSyncProcess($mappingManual)->getSyncReport(1);
    }

    public function testThatMailVotechGetSyncReportIsNotCalledBasedOnRequest(): void
    {
        $objectName    = 'Contact';
        $mappingManual = new MappingManualDAO(self::INTEGRATION_NAME);

        $this->syncDateHelper->expects($this->never())
            ->method('getSyncFromDateTime')
            ->with(self::INTEGRATION_NAME, $objectName);

        // SyncDateExchangeInterface::getSyncReport should sync because an object was added to the report
        $this->syncDataExchange->expects($this->never())
            ->method('getSyncReport');

        $report = $this->createMailVotechSyncProcess($mappingManual)->getSyncReport(1);

        $this->assertEquals(MailVotechSyncDataExchange::NAME, $report->getIntegration());
    }

    public function testGetSyncOrder(): void
    {
        $objectName    = 'Contact';
        $mappingManual = new MappingManualDAO(self::INTEGRATION_NAME);
        $objectMapping = new ObjectMappingDAO(Contact::NAME, $objectName);
        $objectMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $objectMapping->addFieldMapping('firstname', 'first_name');
        $mappingManual->addObjectMapping($objectMapping);

        $toSyncDateTime = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncDateTime')
            ->willReturn($toSyncDateTime);

        $syncReport = new ReportDAO(self::INTEGRATION_NAME);
        $objectDAO  = new ReportObjectDAO($objectName, 2);
        $objectDAO->addField(new ReportFieldDAO('email', new NormalizedValueDAO(NormalizedValueDAO::EMAIL_TYPE, 'test@test.com')));
        $objectDAO->addField(new ReportFieldDAO('first_name', new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'Bob')));
        $syncReport->addObject($objectDAO);

        // Search for an internal object
        $this->syncDataExchange->expects($this->once())
            ->method('getConflictedInternalObject')
            ->with($mappingManual, Contact::NAME, $objectDAO)
            ->willReturn(
                new ReportObjectDAO(Contact::NAME, 1)
            );

        $objectChangeDAO = new ObjectChangeDAO(MailVotechSyncDataExchange::NAME, Contact::NAME, 1, $objectName, 2);
        $objectChangeDAO->addField(new OrderFieldDAO('email', new NormalizedValueDAO(NormalizedValueDAO::EMAIL_TYPE, 'test@test.com')));
        $objectChangeDAO->addField(new OrderFieldDAO('firstname', new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'Bob')));
        $this->objectChangeGenerator->expects($this->once())
            ->method('getSyncObjectChange')
            ->willReturn($objectChangeDAO);

        $syncOrder = $this->createMailVotechSyncProcess($mappingManual)->getSyncOrder($syncReport);

        // The change should have been added to the order as an identified object
        $this->assertEquals([Contact::NAME => [1 => $objectChangeDAO]], $syncOrder->getIdentifiedObjects());
    }

    public function testGetSyncOrderObjectDeleted(): void
    {
        $objectName    = 'Contact';
        $mappingManual = new MappingManualDAO(self::INTEGRATION_NAME);
        $objectMapping = new ObjectMappingDAO(Contact::NAME, $objectName);
        $objectMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $objectMapping->addFieldMapping('firstname', 'first_name');
        $mappingManual->addObjectMapping($objectMapping);

        $toSyncDateTime = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncDateTime')
            ->willReturn($toSyncDateTime);

        $syncReport       = new ReportDAO(self::INTEGRATION_NAME);
        $reportObjectDAO  = new ReportObjectDAO($objectName, 2);
        $reportObjectDAO->addField(new ReportFieldDAO('email', new NormalizedValueDAO(NormalizedValueDAO::EMAIL_TYPE, 'test@test.com')));
        $reportObjectDAO->addField(new ReportFieldDAO('first_name', new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'Bob')));
        $syncReport->addObject($reportObjectDAO);

        // Search for an internal object
        $this->syncDataExchange->expects($this->once())
            ->method('getConflictedInternalObject')
            ->with($mappingManual, Contact::NAME, $reportObjectDAO)
            ->willThrowException(new ObjectDeletedException());

        $syncOrder = $this->createMailVotechSyncProcess($mappingManual)->getSyncOrder($syncReport);
        $this->assertSame([], $syncOrder->getIdentifiedObjects());
    }

    public function testGetSyncOrderObjectSkipped(): void
    {
        $objectName    = 'Contact';
        $mappingManual = new MappingManualDAO(self::INTEGRATION_NAME);
        $objectMapping = new ObjectMappingDAO(Contact::NAME, $objectName);
        $objectMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $objectMapping->addFieldMapping('firstname', 'first_name');
        $mappingManual->addObjectMapping($objectMapping);

        $toSyncDateTime = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncDateTime')
            ->willReturn($toSyncDateTime);

        $syncReport       = new ReportDAO(self::INTEGRATION_NAME);
        $reportObjectDAO  = new ReportObjectDAO($objectName, 2);
        $reportObjectDAO->addField(new ReportFieldDAO('email', new NormalizedValueDAO(NormalizedValueDAO::EMAIL_TYPE, 'test@test.com')));
        $reportObjectDAO->addField(new ReportFieldDAO('first_name', new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'Bob')));
        $syncReport->addObject($reportObjectDAO);

        // Search for an internal object
        $this->syncDataExchange->expects($this->once())
            ->method('getConflictedInternalObject')
            ->with($mappingManual, Contact::NAME, $reportObjectDAO)
            ->willReturn(
                new ReportObjectDAO(Contact::NAME, 1)
            );

        $objectChangeDAO = new ObjectChangeDAO(MailVotechSyncDataExchange::NAME, Contact::NAME, 1, $objectName, 2);
        $objectChangeDAO->addField(new OrderFieldDAO('email', new NormalizedValueDAO(NormalizedValueDAO::EMAIL_TYPE, 'test@test.com')));
        $objectChangeDAO->addField(new OrderFieldDAO('firstname', new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'Bob')));
        $this->objectChangeGenerator->expects($this->once())
            ->method('getSyncObjectChange')
            ->willThrowException(new ObjectSyncSkippedException());

        $syncOrder = $this->createMailVotechSyncProcess($mappingManual)->getSyncOrder($syncReport);

        $this->assertSame([], $syncOrder->getIdentifiedObjects());
    }

    public function testThatItDoesntSyncOtherEntityTypesWhenIDsForSomeEntityAreSpecified(): void
    {
        $mappingManual         = new MappingManualDAO(self::INTEGRATION_NAME);
        $this->inputOptionsDAO = new InputOptionsDAO([
            'integration'      => self::INTEGRATION_NAME,
            'mailvotech-object-id' => ['contact:1'],
        ]);

        $contactMapping = new ObjectMappingDAO(Contact::NAME, 'Contact');
        $contactMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $mappingManual->addObjectMapping($contactMapping);

        $leadMapping = new ObjectMappingDAO(Contact::NAME, 'Lead');
        $leadMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $mappingManual->addObjectMapping($leadMapping);

        $companyMapping = new ObjectMappingDAO(Company::NAME, 'Account');
        $companyMapping->addFieldMapping('email', 'email', ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
        $mappingManual->addObjectMapping($companyMapping);

        $fromSyncDateTime = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncFromDateTime')
            ->with(MailVotechSyncDataExchange::NAME, Contact::NAME)
            ->willReturn($fromSyncDateTime);

        $toSyncDateTime   = new \DateTimeImmutable();
        $this->syncDateHelper->expects($this->once())
            ->method('getSyncToDateTime')
            ->willReturn($toSyncDateTime);

        $this->syncDataExchange->expects($this->once())
            ->method('getSyncReport')
            ->willReturnCallback(
                function (RequestDAO $requestDAO): ReportDAO {
                    $requestObjects = $requestDAO->getObjects();
                    $this->assertCount(1, $requestObjects);

                    /** @var ObjectDAO $requestObject */
                    $requestObject = $requestObjects[0];
                    $this->assertEquals(['email'], $requestObject->getRequiredFields());
                    $this->assertEquals(Contact::NAME, $requestObject->getObject());

                    return new ReportDAO(self::INTEGRATION_NAME);
                }
            );

        $syncReport = $this->createMailVotechSyncProcess($mappingManual)->getSyncReport(1);
        $this->assertEquals(self::INTEGRATION_NAME, $syncReport->getIntegration());
    }

    private function createMailVotechSyncProcess(MappingManualDAO $mappingManualDAO): MailVotechSyncProcess
    {
        $mailvotechSyncProcess = new MailVotechSyncProcess(
            $this->syncDateHelper,
            $this->objectChangeGenerator,
        );

        $mailvotechSyncProcess->setupSync(
            $this->inputOptionsDAO,
            $mappingManualDAO,
            $this->syncDataExchange
        );

        return $mailvotechSyncProcess;
    }
}
