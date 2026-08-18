<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\Form\Type\ConfigType;
use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
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
            'bundle'     => 'AssetBundle',
            'formAlias'  => 'assetconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MailVotechAsset/FormTheme/Config/_config_assetconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MailVotechAssetBundle'),
        ]);
    }
}
