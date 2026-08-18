<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\Form\Type\CampaignEventAddRemoveLeadType;
use MailVotech\CampaignBundle\Helper\CampaignEventHelper;
use MailVotech\PointBundle\Event\TriggerBuilderEvent;
use MailVotech\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PointSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
        ];
    }

    public function onTriggerBuild(TriggerBuilderEvent $event): void
    {
        $changeLists = [
            'group'    => 'mailvotech.campaign.point.trigger',
            'label'    => 'mailvotech.campaign.point.trigger.changecampaigns',
            'callback' => [CampaignEventHelper::class, 'addRemoveLead'],
            'formType' => CampaignEventAddRemoveLeadType::class,
        ];

        $event->addEvent('campaign.changecampaign', $changeLists);
    }
}
