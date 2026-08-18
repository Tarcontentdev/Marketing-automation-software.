<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelBroadcastEvent;
use MailVotech\SmsBundle\Broadcast\BroadcastExecutioner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class BroadcastSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private BroadcastExecutioner $broadcastExecutioner,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::CHANNEL_BROADCAST => ['onBroadcast', 0],
        ];
    }

    public function onBroadcast(ChannelBroadcastEvent $event): void
    {
        if (!$event->checkContext('sms')) {
            return;
        }

        $this->broadcastExecutioner->execute($event);
    }
}
