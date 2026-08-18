<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\SmsBundle\Form\Type\ConfigType;
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
            'bundle'     => 'SmsBundle',
            'formAlias'  => 'smsconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MailVotechSms/FormTheme/Config/_config_smsconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechSmsBundle'),
        ]);
    }
}
