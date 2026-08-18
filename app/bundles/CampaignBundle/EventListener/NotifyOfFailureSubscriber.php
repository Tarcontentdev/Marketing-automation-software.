<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\NotifyOfFailureEvent;
use MailVotech\CampaignBundle\Executioner\Helper\NotificationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NotifyOfFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationHelper $notificationHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::ON_CAMPAIGN_FAILURE_NOTIFY => 'notifyOfFailure',
        ];
    }

    public function notifyOfFailure(NotifyOfFailureEvent $event): void
    {
        $this->notificationHelper->notifyOfFailure($event->getLead(), $event->getFailedEvent());
    }
}
