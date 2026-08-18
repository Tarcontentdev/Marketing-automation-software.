<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\EventListener;

use MailVotech\PluginBundle\Form\Type\IntegrationsListType;
use MailVotech\PluginBundle\Helper\EventHelper;
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
        $action = [
            'group'     => 'mailvotech.plugin.point.action',
            'label'     => 'mailvotech.plugin.actions.push_lead',
            'formType'  => IntegrationsListType::class,
            // 'formTheme' => 'MailVotechPluginBundle:FormTheme:Integration',
            'callback'  => [EventHelper::class, 'pushLead'],
        ];

        $event->addEvent('plugin.leadpush', $action);
    }
}
