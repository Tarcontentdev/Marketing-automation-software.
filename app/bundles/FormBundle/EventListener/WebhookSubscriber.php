<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\WebhookBundle\Event\WebhookBuilderEvent;
use MailVotech\WebhookBundle\Model\WebhookModel;
use MailVotech\WebhookBundle\WebhookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class WebhookSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private WebhookModel $webhookModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WebhookEvents::WEBHOOK_ON_BUILD => ['onWebhookBuild', 0],
            FormEvents::FORM_ON_SUBMIT      => ['onFormSubmit', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event): void
    {
        // add checkbox to the webhook form for new leads
        $formSubmit = [
            'label'       => 'mailvotech.form.webhook.event.form.submit',
            'description' => 'mailvotech.form.webhook.event.form.submit_desc',
        ];

        // add it to the list
        $event->addEvent(FormEvents::FORM_ON_SUBMIT, $formSubmit);
    }

    public function onFormSubmit(SubmissionEvent $event): void
    {
        $this->webhookModel->queueWebhooksByType(
            FormEvents::FORM_ON_SUBMIT,
            [
                'submission' => $event->getSubmission(),
            ],
            [
                'submissionDetails',
                'ipAddress',
                'leadList',
                'pageList',
                'formList',
            ]
        );
    }
}
