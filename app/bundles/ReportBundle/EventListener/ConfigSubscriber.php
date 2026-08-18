<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\ReportBundle\Form\Type\ConfigType;
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
            'bundle'     => 'ReportBundle',
            'formAlias'  => 'reportconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MailVotechReport/FormTheme/Config/_config_reportconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechReportBundle'),
        ]);
    }
}
