<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\NotificationBundle\Entity\Notification;

final class NotificationEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Notification $notification, $isNew = false)
    {
        $this->entity = $notification;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Notification entity.
     *
     * @return Notification
     */
    public function getNotification()
    {
        return $this->entity;
    }

    /**
     * Sets the Notification entity.
     */
    public function setNotification(Notification $notification): void
    {
        $this->entity = $notification;
    }
}
