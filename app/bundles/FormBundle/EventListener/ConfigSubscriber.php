<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\FormBundle\Form\Type\ConfigFormType;
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
            'bundle'     => 'FormBundle',
            'formAlias'  => 'formconfig',
            'formType'   => ConfigFormType::class,
            'formTheme'  => '@MailVotechForm/FormTheme/Config/_config_formconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechFormBundle'),
        ]);
    }
}
