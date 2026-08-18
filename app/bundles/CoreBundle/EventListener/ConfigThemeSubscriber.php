<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\CoreBundle\Form\Type\ConfigThemeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConfigThemeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        $event->addForm(
            [
                'bundle'     => 'CoreBundle',
                'formAlias'  => 'themeconfig',
                'formType'   => ConfigThemeType::class,
                'formTheme'  => '@MailVotechCore/FormTheme/Config/_config_themeconfig_widget.html.twig',
                'parameters' => [
                    'theme'                           => $event->getParametersFromConfig('MailVotechCoreBundle')['theme'],
                    'theme_import_allowed_extensions' => $event->getParametersFromConfig('MailVotechCoreBundle')['theme_import_allowed_extensions'],
                    'brand_name'                      => $event->getParametersFromConfig('MailVotechCoreBundle')['brand_name'] ?? '',
                    'primary_brand_color'             => $event->getParametersFromConfig('MailVotechCoreBundle')['primary_brand_color'] ?? '000000',
                    'rounded_corners'                 => $event->getParametersFromConfig('MailVotechCoreBundle')['rounded_corners'] ?? '0',
                ],
            ]
        );
    }
}
