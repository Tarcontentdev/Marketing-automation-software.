<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\NotificationBundle\Form\Type\NotificationConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConfigSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        $event->addForm([
            'bundle'     => 'NotificationBundle',
            'formAlias'  => 'notification_config',
            'formType'   => NotificationConfigType::class,
            'formTheme'  => '@MailVotechNotification/FormTheme/Config/_config_notification_config_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechNotificationBundle'),
        ]);
    }
}
