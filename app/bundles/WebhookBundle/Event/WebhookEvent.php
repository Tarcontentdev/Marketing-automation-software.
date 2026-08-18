<?php

namespace MailVotech\WebhookBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\WebhookBundle\Entity\Webhook;

class WebhookEvent extends CommonEvent
{
    /**
     * @var Webhook
     */
    protected $entity;

    /**
     * @param bool   $isNew
     * @param string $reason
     */
    public function __construct(
        Webhook $webhook,
        protected $isNew = false,
        private $reason = '',
    ) {
        $this->entity = $webhook;
    }

    /**
     * Returns the Webhook entity.
     *
     * @return Webhook
     */
    public function getWebhook()
    {
        return $this->entity;
    }

    /**
     * Sets the Webhook entity.
     */
    public function setWebhook(Webhook $webhook): void
    {
        $this->entity = $webhook;
    }

    public function setReason($reason): void
    {
        $this->reason = $reason;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
