<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\NotifyOfUnpublishEvent;
use MailVotech\CampaignBundle\Executioner\Helper\NotificationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NotifyOfUnpublishSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationHelper $notificationHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::ON_CAMPAIGN_UNPUBLISH_NOTIFY => 'notifyOfUnpublish',
        ];
    }

    public function notifyOfUnpublish(NotifyOfUnpublishEvent $event): void
    {
        $this->notificationHelper->notifyOfUnpublish($event->getFailedEvent());
    }
}
