<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\EventListener;

use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\SocialLoginType;
use MailVotechPlugin\MailVotechSocialBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class FormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Config $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD => ['onFormBuild', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }
        $action = [
            'label'          => 'mailvotech.plugin.actions.socialLogin',
            'formType'       => SocialLoginType::class,
            'template'       => '@MailVotechSocial/Integration/login.html.twig',
            'builderOptions' => [
                'addLeadFieldList' => false,
                'addIsRequired'    => false,
                'addDefaultValue'  => false,
                'addSaveResult'    => false,
            ],
        ];

        $event->addFormField('plugin.loginSocial', $action);
    }
}
