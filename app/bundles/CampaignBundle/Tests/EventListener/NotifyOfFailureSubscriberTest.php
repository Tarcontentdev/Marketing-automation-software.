<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\EventListener;

use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Event\NotifyOfFailureEvent;
use MailVotech\CampaignBundle\EventListener\NotifyOfFailureSubscriber;
use MailVotech\CampaignBundle\Executioner\Helper\NotificationHelper;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotifyOfFailureSubscriberTest extends TestCase
{
    private MockObject&NotificationHelper $notificationHelper;

    private NotifyOfFailureSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->notificationHelper = $this->createMock(NotificationHelper::class);
        $this->subscriber         = new NotifyOfFailureSubscriber($this->notificationHelper);
    }

    public function testNotifyOfFailure(): void
    {
        $lead  = new Lead();
        $event = $this->createStub(Event::class);

        $notifyEvent = new NotifyOfFailureEvent($lead, $event);

        // Mock the notifyOfFailure method to expect the lead and event
        $this->notificationHelper->expects($this->once())
            ->method('notifyOfFailure')
            ->with(
                $lead,
                $event
            );

        $this->subscriber->notifyOfFailure($notifyEvent);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = NotifyOfFailureSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey('mailvotech.campaign_failure_notify', $events);
        $this->assertEquals('notifyOfFailure', $events['mailvotech.campaign_failure_notify']);
    }
}
