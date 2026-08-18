<?php

namespace MailVotech\PluginBundle\EventListener;

use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\PluginBundle\Form\Type\IntegrationsListType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FormSubscriber implements EventSubscriberInterface
{
    use PushToIntegrationTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD            => ['onFormBuild', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION => ['onFormSubmitActionTriggered', 0],
        ];
    }

    public function onFormBuild(FormBuilderEvent $event): void
    {
        $event->addSubmitAction('plugin.leadpush', [
            'group'       => 'mailvotech.plugin.actions',
            'description' => 'mailvotech.plugin.actions.tooltip',
            'label'       => 'mailvotech.plugin.actions.push_lead',
            'formType'    => IntegrationsListType::class,
            'formTheme'   => '@MailVotechPlugin/FormTheme/Integration/layout.html.twig',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechPlugin/Action/integration.html.twig',
        ]);
    }

    public function onFormSubmitActionTriggered(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('plugin.leadpush')) {
            return;
        }

        $this->pushToIntegration($event->getActionConfig(), $event->getSubmission()->getLead());
    }
}
