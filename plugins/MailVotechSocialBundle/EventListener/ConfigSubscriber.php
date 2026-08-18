<?php

namespace MailVotechPlugin\MailVotechSocialBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\ConfigBundle\Event\ConfigEvent;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\ConfigType;
use MailVotechPlugin\MailVotechSocialBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ConfigSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Config $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
            ConfigEvents::CONFIG_PRE_SAVE    => ['onConfigSave', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }
        $event->addForm(
            [
                'formAlias'  => 'social_config',
                'formTheme'  => '@MailVotechSocial/FormTheme/Config/_config_social_config_widget.html.twig',
                'formType'   => ConfigType::class,
                'parameters' => $event->getParametersFromConfig('MailVotechSocialBundle'),
            ]
        );
    }

    public function onConfigSave(ConfigEvent $event): void
    {
        /** @var array $values */
        $values = $event->getConfig();

        // Manipulate the values
        if (!empty($values['social_config']['twitter_handle_field'])) {
            $values['social_config']['twitter_handle_field'] = htmlspecialchars($values['social_config']['twitter_handle_field']);
        }

        // Set updated values
        $event->setConfig($values);
    }
}
