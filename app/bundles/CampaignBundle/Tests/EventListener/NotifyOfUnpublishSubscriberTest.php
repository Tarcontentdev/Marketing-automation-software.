<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\EventListener;

use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Event\NotifyOfUnpublishEvent;
use MailVotech\CampaignBundle\EventListener\NotifyOfUnpublishSubscriber;
use MailVotech\CampaignBundle\Executioner\Helper\NotificationHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotifyOfUnpublishSubscriberTest extends TestCase
{
    private MockObject&NotificationHelper $notificationHelper;

    private NotifyOfUnpublishSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->notificationHelper = $this->createMock(NotificationHelper::class);
        $this->subscriber         = new NotifyOfUnpublishSubscriber($this->notificationHelper);
    }

    public function testNotifyOfUnpublish(): void
    {
        $event = $this->createStub(Event::class);

        $notifyEvent = new NotifyOfUnpublishEvent($event);

        // Mock the notifyOfUnpublish method to expect the event
        $this->notificationHelper->expects($this->once())
            ->method('notifyOfUnpublish')
            ->with(
                $event
            );

        $this->subscriber->notifyOfUnpublish($notifyEvent);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = NotifyOfUnpublishSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey('mailvotech.campaign_unpublish_notify', $events);
        $this->assertEquals('notifyOfUnpublish', $events['mailvotech.campaign_unpublish_notify']);
    }
}
