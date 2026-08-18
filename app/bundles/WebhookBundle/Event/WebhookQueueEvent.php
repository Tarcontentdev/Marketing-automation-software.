<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Entity\WebhookQueue;

final class WebhookQueueEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(
        WebhookQueue $webhookQueue,
        private Webhook $webhook,
        $isNew = false,
    ) {
        $this->entity  = $webhookQueue;
        $this->isNew   = $isNew;
    }

    /**
     * Returns the WebhookQueue entity.
     *
     * @return WebhookQueue
     */
    public function getWebhookQueue()
    {
        return $this->entity;
    }

    /**
     * Sets the WebhookQueue entity.
     */
    public function setWebhookQueue(WebhookQueue $webhookQueue): void
    {
        $this->entity = $webhookQueue;
    }

    /**
     * Returns the Webhook entity.
     */
    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    /**
     * Sets the Webhook entity.
     */
    public function setWebhook(Webhook $webhook): void
    {
        $this->webhook = $webhook;
    }
}
