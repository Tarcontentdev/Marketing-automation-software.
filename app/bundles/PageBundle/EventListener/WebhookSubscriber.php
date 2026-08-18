<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\EventListener;

use MailVotech\PageBundle\Event\PageHitEvent;
use MailVotech\PageBundle\PageEvents;
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
            PageEvents::PAGE_ON_HIT         => ['onPageHit', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onWebhookBuild(WebhookBuilderEvent $event): void
    {
        // add checkbox to the webhook form for new leads
        $pageHit = [
            'label'       => 'mailvotech.page.webhook.event.hit',
            'description' => 'mailvotech.page.webhook.event.hit_desc',
        ];

        // add it to the list
        $event->addEvent(PageEvents::PAGE_ON_HIT, $pageHit);
    }

    public function onPageHit(PageHitEvent $event): void
    {
        $this->webhookModel->queueWebhooksByType(
            PageEvents::PAGE_ON_HIT,
            [
                'hit' => $event->getHit(),
            ],
            [
                'hitDetails',
                'emailDetails',
                'pageList',
                'leadList',
            ]
        );
    }
}
