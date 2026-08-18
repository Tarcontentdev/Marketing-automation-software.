<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\EmailBundle\Entity\Email;

final class EmailEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Email &$email, $isNew = false)
    {
        $this->entity = &$email;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Email entity.
     *
     * @return Email
     */
    public function getEmail()
    {
        return $this->entity;
    }

    /**
     * Sets the Email entity.
     */
    public function setEmail(Email $email): void
    {
        $this->entity = $email;
    }
}
