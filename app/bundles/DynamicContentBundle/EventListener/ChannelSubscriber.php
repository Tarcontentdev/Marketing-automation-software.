<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\EventListener;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\ReportBundle\Model\ReportModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ChannelSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 0],
        ];
    }

    public function onAddChannel(ChannelEvent $event): void
    {
        $event->addChannel(
            'dynamicContent',
            [
                ReportModel::CHANNEL_FEATURE => [
                    'table' => 'dynamic_content',
                ],
            ]
        );
    }
}
