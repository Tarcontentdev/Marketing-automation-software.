<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\MessengerBundle\Form\Type\ConfigType;
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
            'bundle'     => 'MessengerBundle',
            'formAlias'  => 'messengerconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MailVotechMessenger/FormTheme/Config/_config_messengerconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechMessengerBundle'),
        ]);
    }
}
