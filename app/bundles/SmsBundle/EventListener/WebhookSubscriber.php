<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\SmsBundle\Event\SmsSendEvent;
use MailVotech\SmsBundle\SmsEvents;
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
            SmsEvents::SMS_ON_SEND          => 'onSend',
            WebhookEvents::WEBHOOK_ON_BUILD => 'onWebhookBuild',
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event): void
    {
        $event->addEvent(
            SmsEvents::SMS_ON_SEND,
            [
                'label'       => 'mailvotech.sms.webhook.event.send',
                'description' => 'mailvotech.sms.webhook.event.send_desc',
            ]
        );
    }

    public function onSend(SmsSendEvent $event): void
    {
        $this->webhookModel->queueWebhooksByType(
            SmsEvents::SMS_ON_SEND,
            [
                'smsId'   => $event->getSmsId(),
                'contact' => $event->getLead(),
                'content' => $event->getContent(),
            ]
        );
    }
}
