<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Event;

use MailVotech\ChannelBundle\Entity\MessageQueue;
use MailVotech\CoreBundle\Event\CommonEvent;

final class MessageQueueEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(MessageQueue $entity, $isNew = false)
    {
        $this->entity = $entity;
        $this->isNew  = $isNew;
    }

    /**
     * @return MessageQueue
     */
    public function getMessageQueue()
    {
        return $this->entity;
    }

    /**
     * @param MessageQueue $entity
     */
    public function setMessageQueue($entity): void
    {
        $this->entity = $entity;
    }
}
