<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Event;

use MailVotech\CoreBundle\Event\BuilderEvent;
use MailVotech\EmailBundle\Entity\Email;

final class EmailBuilderEvent extends BuilderEvent
{
    /**
     * @return Email|null
     */
    public function getEmail()
    {
        return $this->entity;
    }
}
