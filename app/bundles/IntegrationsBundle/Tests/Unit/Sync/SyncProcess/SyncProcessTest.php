<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncProcess;

use MailVotech\IntegrationsBundle\Entity\ObjectMapping;
use MailVotech\IntegrationsBundle\Event\CompletedSyncIterationEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\RemappedObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectMappingsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\RelationsHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\Notification\Notifier;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MailVotechSyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\SyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncService\SyncServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SyncProcessTest extends TestCase
{
    /**
     * @var MockObject&MailVotechSyncDataExchange
     */
    private MockObject $internalSyncDataExchange;

    /**
     * @var MockObject&SyncDateHelper
     */
    private MockObject $syncDateHelper;

    /**
     * @var MockObject&IntegrationSyncProcess
     */
    private MockObject $integrationSyncProcess;

    /**
     * @var MockObject&MailVotechSyncProcess
     */
    private MockObject $mailvotechSyncProcess;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $eventDispatcher;

    /**
     * @var MockObject&InputOptionsDAO
     */
    private MockObject $inputOptionsDAO;

    private SyncProcess $syncProcess;

    protected function setUp(): void
    {
        $this->syncDateHelper              = $this->createMock(SyncDateHelper::class);
        $this->integrationSyncProcess      = $this->createMock(IntegrationSyncProcess::class);
        $this->mailvotechSyncProcess           = $this->createMock(MailVotechSyncProcess::class);
        $this->eventDispatcher             = $this->createMock(EventDispatcherInterface::class);
        $this->internalSyncDataExchange    = $this->createMock(MailVotechSyncDataExchange::class);
        $this->inputOptionsDAO             = $this->createMock(InputOptionsDAO::class);

        $this->syncProcess = new SyncProcess(
            $this->syncDateHelper,
            $this->createStub(MappingHelper::class),
            $this->createStub(RelationsHelper::class),
            $this->integrationSyncProcess,
            $this->mailvotechSyncProcess,
            $this->eventDispatcher,
            $this->createStub(Notifier::class),
            $this->createStub(MappingManualDAO::class),
            $this->internalSyncDataExchange,
            $this->createStub(SyncDataExchangeInterface::class),
            $this->inputOptionsDAO,
            $this->createStub(SyncServiceInterface::class)
        );
    }

    public function testBatchSyncEventsAreDispatched(): void
    {
        $this->inputOptionsDAO->expects($this->once())
            ->method('pullIsEnabled')
            ->willReturn(true);

        $this->inputOptionsDAO->expects($this->once())
            ->method('pushIsEnabled')
            ->willReturn(true);

        $this->syncDateHelper->expects($this->once())
            ->method('setInternalSyncStartDateTime');

        // Integration to MailVotech

        // fetch the report from the integration
        $integrationSyncReport = $this->createMock(ReportDAO::class);
        $integrationSyncReport->expects($this->exactly(2))
            ->method('shouldSync')
            ->willReturnOnConsecutiveCalls(true, false);
        $matcher = $this->exactly(2);
        $this->integrationSyncProcess->expects($matcher)
            ->method('getSyncReport')->willReturnCallback(function (...$parameters) use ($matcher, $integrationSyncReport): MockObject {
                if (1 === $matcher->numberOfInvocations()) {
                    $this->assertSame(1, $parameters[0]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $this->assertSame(2, $parameters[0]);
                }

                return $integrationSyncReport;
            });

        // generate the order based on the report
        $integrationSyncOrder = $this->createMock(OrderDAO::class);
        $integrationSyncOrder->expects($this->once())
            ->method('shouldSync')
            ->willReturn(true);
        $this->mailvotechSyncProcess->expects($this->once())
            ->method('getSyncOrder')
            ->with($integrationSyncReport)
            ->willReturn($integrationSyncOrder);
        $integrationSyncOrder->expects($this->once())
            ->method('getDeletedObjects')
            ->willReturn([new ObjectChangeDAO('foobar', 'foo', 'foo1', 'contact', 1)]);
        $integrationSyncOrder->expects($this->once())
            ->method('getRemappedObjects')
            ->willReturn([new RemappedObjectDAO('foobar', 'foo', 'foo1', 'bar', 'bar1')]);

        // execute the order
        $objectMappings = $this->createMock(ObjectMappingsDAO::class);
        $objectMappings->expects($this->once())
            ->method('getNewMappings')
            ->willReturn([(new ObjectMapping())->setIntegrationObjectName('foo')]);
        $objectMappings->expects($this->once())
            ->method('getUpdatedMappings')
            ->willReturn([(new ObjectMapping())->setIntegrationObjectName('bar')]);
        $this->internalSyncDataExchange->expects($this->once())
            ->method('executeSyncOrder')
            ->willReturn($objectMappings);
        $matcher = $this->any();

        $this->eventDispatcher->expects($matcher)
            ->method('dispatch')->willReturnCallback(function (...$parameters) use ($matcher): object {
                if (1 === $matcher->numberOfInvocations()) {
                    $callback = function (CompletedSyncIterationEvent $event): void {
                        $orderResult = $event->getOrderResults();
                        $this->assertCount(1, $orderResult->getUpdatedObjectMappings('bar'));
                        $this->assertCount(1, $orderResult->getNewObjectMappings('foo'));
                        $this->assertCount(1, $orderResult->getDeletedObjects('foo'));
                        $this->assertCount(1, $orderResult->getRemappedObjects('bar'));
                    };
                    $callback($parameters[0]);
                    $this->assertSame(IntegrationEvents::INTEGRATION_BATCH_SYNC_COMPLETED_INTEGRATION_TO_MAILVOTECH, $parameters[1]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $callback = function (CompletedSyncIterationEvent $event): void {
                        $orderResult = $event->getOrderResults();
                        $this->assertCount(1, $orderResult->getNewObjectMappings('bar'));
                        $this->assertCount(1, $orderResult->getUpdatedObjectMappings('foo'));
                    };
                    $callback($parameters[0]);
                    $this->assertSame(IntegrationEvents::INTEGRATION_BATCH_SYNC_COMPLETED_MAILVOTECH_TO_INTEGRATION, $parameters[1]);
                }

                return $parameters[0];
            });

        // MailVotech to integration

        // fetch the report from MailVotech
        $internalSyncReport = $this->createMock(ReportDAO::class);
        $internalSyncReport->expects($this->exactly(2))
            ->method('shouldSync')
            ->willReturnOnConsecutiveCalls(true, false);
        $matcher = $this->exactly(2);
        $this->mailvotechSyncProcess->expects($matcher)
            ->method('getSyncReport')->willReturnCallback(function (...$parameters) use ($matcher, $internalSyncReport): MockObject {
                if (1 === $matcher->numberOfInvocations()) {
                    $this->assertSame(1, $parameters[0]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $this->assertSame(2, $parameters[0]);
                }

                return $internalSyncReport;
            });

        // generate the order based on the report
        $internalSyncOrder = $this->createMock(OrderDAO::class);
        $internalSyncOrder->expects($this->once())
            ->method('shouldSync')
            ->willReturn(true);
        $internalSyncOrder->expects($this->exactly(2))
            ->method('getObjectMappings')
            ->willReturn([(new ObjectMapping())->setIntegrationObjectName('bar')]);
        $updatedObjectMapping = new UpdatedObjectMappingDAO('foobar', 'foo', 'foo1', new \DateTime());
        $updatedObjectMapping->setObjectMapping((new ObjectMapping())->setIntegrationObjectName('foo'));

        // Test that getOrderResultsForInternalSync ignores an object with a missing ObjectMapping
        $updatedObjectMapping2 = new UpdatedObjectMappingDAO('foobar', 'foo', 'foo2', new \DateTime());

        $internalSyncOrder->expects($this->exactly(2))
            ->method('getUpdatedObjectMappings')
            ->willReturn([$updatedObjectMapping, $updatedObjectMapping2]);
        $internalSyncOrder->expects($this->exactly(2))
            ->method('getDeletedObjects')
            ->willReturn([]); // currently not supported for MailVotech to integration
        $internalSyncOrder->expects($this->exactly(2))
            ->method('getRemappedObjects')
            ->willReturn([]); // currently not supported for MailVotech to integration
        $internalSyncOrder->expects($this->once())
            ->method('getNotifications')
            ->willReturn([]);
        $internalSyncOrder->expects($this->once())
            ->method('getSuccessfullySyncedObjects')
            ->willReturn([]);

        $this->integrationSyncProcess->expects($this->once())
            ->method('getSyncOrder')
            ->with($internalSyncReport)
            ->willReturn($internalSyncOrder);

        // execute the order
        $this->internalSyncDataExchange->expects($this->once())
            ->method('executeSyncOrder')
            ->willReturn($objectMappings);

        $this->syncProcess->execute();
    }
}
