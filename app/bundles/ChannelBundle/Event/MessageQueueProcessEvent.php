<?php

namespace MailVotech\ChannelBundle\Event;

use MailVotech\ChannelBundle\Entity\MessageQueue;
use MailVotech\CoreBundle\Event\CommonEvent;

final class MessageQueueProcessEvent extends CommonEvent
{
    public function __construct(MessageQueue $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return MessageQueue
     */
    public function getMessageQueue()
    {
        return $this->entity;
    }

    public function checkContext($channel): bool
    {
        return $channel === $this->entity->getChannel();
    }
}
