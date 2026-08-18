<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\WebhookBundle\Form\Type\ConfigType;
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
            'bundle'     => 'WebhookBundle',
            'formAlias'  => 'webhookconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MailVotechWebhook/FormTheme/Config/_config_webhookconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechWebhookBundle'),
        ]);
    }
}
