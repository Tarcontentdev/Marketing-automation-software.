<?php

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\NotificationBundle\Entity\PushID;
use MailVotech\NotificationBundle\NotificationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CampaignConditionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD                 => ['onCampaignBuild', 0],
            NotificationEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerHasActiveCondition', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addCondition(
            'notification.has.active',
            [
                'label'       => 'mailvotech.notification.campaign.event.notification.has.active',
                'description' => 'mailvotech.notification.campaign.event.notification.has.active.desc',
                'eventName'   => NotificationEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            ]
        );
    }

    public function onCampaignTriggerHasActiveCondition(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('notification.has.active')) {
            return;
        }

        $pushIds = $event->getLead()->getPushIDs();
        /** @var PushID $pushID */
        foreach ($pushIds as $pushID) {
            if ($pushID->isEnabled()) {
                $event->setResult(true);

                return;
            }
        }

        $event->setResult(false);
    }
}
