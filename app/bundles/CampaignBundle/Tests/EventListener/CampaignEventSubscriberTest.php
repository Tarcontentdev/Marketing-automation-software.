<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\EventRepository;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Event\CampaignEvent;
use MailVotech\CampaignBundle\Event\ExecutedEvent;
use MailVotech\CampaignBundle\Event\FailedEvent;
use MailVotech\CampaignBundle\Event\NotifyOfFailureEvent;
use MailVotech\CampaignBundle\Event\NotifyOfUnpublishEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use MailVotech\CampaignBundle\EventListener\CampaignEventSubscriber;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Twig\Helper\DateHelper;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CampaignEventSubscriberTest extends TestCase
{
    private CampaignEventSubscriber $fixture;

    /**
     * @var MockObject&EventRepository
     */
    private MockObject $eventRepo;

    /**
     * @var MockObject&CampaignModel
     */
    private MockObject $campaignModelMock;

    /**
     * @var MockObject&LeadEventLogRepository
     */
    private MockObject $leadEventLogRepositoryMock;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $eventDispatcherMock;

    protected function setUp(): void
    {
        $this->eventRepo                  = $this->createMock(EventRepository::class);
        $this->campaignModelMock          = $this->createMock(CampaignModel::class);
        $this->leadEventLogRepositoryMock = $this->createMock(LeadEventLogRepository::class);
        $this->eventDispatcherMock        = $this->createMock(EventDispatcherInterface::class);
        $dateHelper                       = new DateHelper(
            'F j, Y g:i a T',
            'D, M d',
            'F j, Y',
            'g:i a',
            $this->createStub(TranslatorInterface::class),
            $this->createStub(CoreParametersHelper::class)
        );
        $this->fixture                    = new CampaignEventSubscriber(
            $this->eventRepo,
            $this->campaignModelMock,
            $this->leadEventLogRepositoryMock,
            $this->eventDispatcherMock,
            $dateHelper
        );
    }

    public function testEventFailedCountsGetResetOnCampaignPublish(): void
    {
        $campaign = new Campaign();
        // Ensure the campaign is unpublished
        $campaign->setIsPublished(false);
        // Go from unpublished to published.
        $campaign->setIsPublished(true);

        $this->eventRepo->expects($this->once())
            ->method('resetFailedCountsForEventsInCampaign')
            ->with($campaign);

        $this->fixture->onCampaignPreSave(new CampaignEvent($campaign));
    }

    public function testEventFailedCountsDoesNotGetResetOnCampaignUnPublish(): void
    {
        $campaign = new Campaign();
        // Ensure the campaign is published
        $campaign->setIsPublished(true);
        // Go from published to unpublished.
        $campaign->setIsPublished(false);

        $this->eventRepo->expects($this->never())
            ->method('resetFailedCountsForEventsInCampaign');

        $this->fixture->onCampaignPreSave(new CampaignEvent($campaign));
    }

    public function testEventFailedCountsDoesNotGetResetWhenPublishedStateIsNotChanged(): void
    {
        $campaign = new Campaign();

        $this->eventRepo->expects($this->never())
            ->method('resetFailedCountsForEventsInCampaign');

        $this->fixture->onCampaignPreSave(new CampaignEvent($campaign));
    }

    public function testNewPublishedCampaignGetsPublishUpWithoutSeconds(): void
    {
        $campaign = new Campaign();
        $campaign->setIsPublished(true);

        $this->fixture->onCampaignPreSave(new CampaignEvent($campaign));

        $this->assertInstanceOf(\DateTimeInterface::class, $campaign->getPublishUp());
        $this->assertSame('00', $campaign->getPublishUp()->format('s'));
    }

    public function testFailedEventGeneratesANotification(): void
    {
        $this->leadEventLogRepositoryMock->expects($this->once())
            ->method('isLastFailed')
            ->with(42, 42)
            ->willReturn(false);

        $mockLead     = $this->createMock(Lead::class);
        $mockLead
            ->method('getId')
            ->willReturn(42);
        $mockCampaign = $this->createMock(Campaign::class);
        $mockCampaign->expects($this->once())
            ->method('getLeads')
            ->willReturn(new ArrayCollection(range(0, 99)));

        $mockEvent = $this->createMock(Event::class);
        $mockEvent->expects($this->once())
            ->method('getCampaign')
            ->willReturn($mockCampaign);
        $mockEvent
            ->method('getId')
            ->willReturn(42);

        $mockEventLog = $this->createMock(LeadEventLog::class);
        $mockEventLog->expects($this->once())
            ->method('getEvent')
            ->willReturn($mockEvent);

        $mockEventLog
            ->method('getLead')
            ->willReturn($mockLead);

        $this->eventRepo->expects($this->once())
            ->method('getFailedCountLeadEvent')
            ->withAnyParameters()
            ->willReturn(105);

        // Set failed count to 5% of getLeads()->count()
        $this->eventRepo->expects($this->once())
            ->method('incrementFailedCount')
            ->with($mockEvent)
            ->willReturn(5);

        $this->eventDispatcherMock->expects($this->once())
            ->method('hasListeners')
            ->with(CampaignEvents::ON_CAMPAIGN_FAILURE_NOTIFY)
            ->willReturn(true);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturn(new NotifyOfFailureEvent($mockLead, $mockEvent));

        $failedEvent = new FailedEvent($this->createStub(AbstractEventAccessor::class), $mockEventLog);

        $this->fixture->onEventFailed($failedEvent);
    }

    public function testFailedCountOverDisableCampaignThresholdDisablesTheCampaign(): void
    {
        $this->leadEventLogRepositoryMock->expects($this->once())
            ->method('isLastFailed')
            ->with(42, 42)
            ->willReturn(false);

        $mockLead     = $this->createMock(Lead::class);
        $mockLead
            ->method('getId')
            ->willReturn(42);
        $mockCampaign = $this->createMock(Campaign::class);
        $mockCampaign->expects($this->once())
            ->method('isPublished')
            ->willReturn(true);

        $mockCampaign->expects($this->once())
            ->method('getLeads')
            ->willReturn(new ArrayCollection(range(0, 99)));

        $mockEvent = $this->createMock(Event::class);
        $mockEvent->expects($this->once())
            ->method('getCampaign')
            ->willReturn($mockCampaign);
        $mockEvent
            ->method('getId')
            ->willReturn(42);

        $mockEventLog = $this->createMock(LeadEventLog::class);
        $mockEventLog->expects($this->once())
            ->method('getEvent')
            ->willReturn($mockEvent);

        $mockEventLog
            ->method('getLead')
            ->willReturn($mockLead);

        $this->eventRepo->expects($this->once())
            ->method('getFailedCountLeadEvent')
            ->withAnyParameters()
            ->willReturn(200);

        // Set failed count to 35% of getLeads()->count()
        $this->eventRepo->expects($this->once())
            ->method('incrementFailedCount')
            ->with($mockEvent)
            ->willReturn(35);

        $this->eventDispatcherMock->expects($this->exactly(2))
            ->method('hasListeners')
            ->willReturnMap([
                [CampaignEvents::ON_CAMPAIGN_FAILURE_NOTIFY, true],
                [CampaignEvents::ON_CAMPAIGN_UNPUBLISH_NOTIFY, true],
            ]);

        $this->eventDispatcherMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnOnConsecutiveCalls(
                new NotifyOfFailureEvent($mockLead, $mockEvent),
                new NotifyOfUnpublishEvent($mockEvent)
            );

        $this->campaignModelMock->expects($this->once())
            ->method('transactionalCampaignUnPublish')
            ->with($mockCampaign);

        $failedEvent = new FailedEvent($this->createStub(AbstractEventAccessor::class), $mockEventLog);

        $this->fixture->onEventFailed($failedEvent);
    }

    public function testOnEventExecutedDecreaseTheCounter(): void
    {
        $mockEventLog = $this->createMock(LeadEventLog::class);

        $lead = new Lead();
        $lead->setId(42);

        $eventMock = $this->createMock(Event::class);
        $eventMock
            ->method('getId')
            ->willReturn(42);

        $mockEventLog->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $mockEventLog
            ->method('getLead')
            ->willReturn($lead);

        $this->leadEventLogRepositoryMock->expects($this->once())
            ->method('isLastFailed')
            ->with(42, 42)
            ->willReturn(true);

        $executedEvent = new ExecutedEvent($this->createStub(AbstractEventAccessor::class), $mockEventLog);

        $this->eventRepo->expects($this->once())
            ->method('getFailedCountLeadEvent')
            ->withAnyParameters()
            ->willReturn(101);

        $this->eventRepo->expects($this->once())
            ->method('decreaseFailedCount')
            ->with($eventMock);

        $this->fixture->onEventExecuted($executedEvent);
    }

    public function testOnEventExecutedForDeletedContacts(): void
    {
        $mockEventLog = $this->createMock(LeadEventLog::class);

        $lead            = new Lead();
        $lead->deletedId = 10;

        $eventMock = $this->createMock(Event::class);
        $eventMock
            ->method('getId')
            ->willReturn(1);

        $mockEventLog->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);

        $mockEventLog->expects($this->once())
            ->method('getLead')
            ->willReturn($lead);

        $this->leadEventLogRepositoryMock->expects($this->once())
            ->method('isLastFailed')
            ->with($lead->deletedId, 1)
            ->willReturn(true);

        $executedEvent = new ExecutedEvent($this->createStub(AbstractEventAccessor::class), $mockEventLog);

        $this->eventRepo->expects($this->once())
            ->method('getFailedCountLeadEvent')
            ->with($lead->deletedId, 1)
            ->willReturn(101);

        $this->eventRepo->expects($this->once())
            ->method('decreaseFailedCount')
            ->with($eventMock);

        $this->fixture->onEventExecuted($executedEvent);
    }

    public function testOnFailedEventGeneratesOneUnPublishNotificationAndEmail(): void
    {
        // Set up mocks
        $leadEventLogMock = $this->createMock(LeadEventLog::class);
        $eventMock        = $this->createMock(Event::class);
        $leadEventLogMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $leadMock = $this->createMock(Lead::class);
        $leadEventLogMock->expects($this->once())->method('getLead')->willReturn($leadMock);

        // Set up campaign mock with isPublished returning false to simulate campaign already unpublished
        $campaignMock = $this->createMock(Campaign::class);
        $campaignMock->expects($this->once())->method('isPublished')->willReturn(false);
        $eventMock->method('getCampaign')->willReturn($campaignMock);

        // Mock behavior for threshold calculations
        $leadMock->method('getId')->willReturn(1);
        $eventMock->method('getId')->willReturn(1);
        $this->eventRepo->expects($this->once())->method('getFailedCountLeadEvent')
            ->with(1, 1)->willReturn(101);
        $this->leadEventLogRepositoryMock->expects($this->once())->method('isLastFailed')
            ->with(1, 1)->willReturn(false);
        $this->eventRepo->expects($this->once())->method('incrementFailedCount')
            ->with($eventMock)->willReturn(35);

        // Set up leads collection
        $totalLeads = array_fill(0, 100, new Lead());
        $campaignMock->expects($this->once())->method('getLeads')->willReturn(new ArrayCollection($totalLeads));

        // Expect failure notification to be dispatched
        $this->eventDispatcherMock->expects($this->once())
            ->method('hasListeners')
            ->with(CampaignEvents::ON_CAMPAIGN_FAILURE_NOTIFY)
            ->willReturn(true);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturn(new NotifyOfFailureEvent($leadMock, $eventMock));

        // Unpublish notification should not be dispatched because campaign is already unpublished
        $this->campaignModelMock->expects($this->never())->method('transactionalCampaignUnPublish');

        // Execute the test
        $failedEvent = new FailedEvent($this->createStub(AbstractEventAccessor::class), $leadEventLogMock);
        $this->fixture->onEventFailed($failedEvent);
    }
}
