<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Event;

use MailVotech\ChannelBundle\Entity\Message;
use MailVotech\CoreBundle\Event\CommonEvent;

final class MessageEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Message $message, $isNew = false)
    {
        $this->entity = $message;
        $this->isNew  = $isNew;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->entity;
    }
}
