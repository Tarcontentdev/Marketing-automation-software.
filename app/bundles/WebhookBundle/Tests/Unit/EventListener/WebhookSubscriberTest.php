<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Tests\Unit\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Event\WebhookEvent;
use MailVotech\WebhookBundle\EventListener\WebhookSubscriber;
use MailVotech\WebhookBundle\Notificator\WebhookKillNotificator;
use MailVotech\WebhookBundle\WebhookEvents;
use PHPUnit\Framework\MockObject\MockObject;

final class WebhookSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&WebhookKillNotificator
     */
    private MockObject $webhookKillNotificator;

    protected function setUp(): void
    {
        $this->webhookKillNotificator = $this->createMock(WebhookKillNotificator::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                WebhookEvents::WEBHOOK_POST_SAVE   => ['onWebhookSave', 0],
                WebhookEvents::WEBHOOK_POST_DELETE => ['onWebhookDelete', 0],
                WebhookEvents::WEBHOOK_KILL        => ['onWebhookKill', 0],
            ],
            WebhookSubscriber::getSubscribedEvents()
        );
    }

    public function testOnWebhookKill(): void
    {
        $webhookMock = $this->createStub(Webhook::class);
        $reason      = 'reason';

        $eventMock = $this->createMock(WebhookEvent::class);
        $eventMock
            ->expects($this->once())
            ->method('getWebhook')
            ->willReturn($webhookMock);
        $eventMock
            ->expects($this->once())
            ->method('getReason')
            ->willReturn($reason);

        $this->webhookKillNotificator
            ->expects($this->once())
            ->method('send')
            ->with($webhookMock, $reason);

        $subscriber = new WebhookSubscriber($this->createStub(IpLookupHelper::class), $this->createStub(AuditLogModel::class), $this->webhookKillNotificator);
        $subscriber->onWebhookKill($eventMock);
    }
}
