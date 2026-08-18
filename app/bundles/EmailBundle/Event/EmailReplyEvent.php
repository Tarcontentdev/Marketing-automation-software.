<?php

namespace MailVotech\EmailBundle\Event;

use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use Symfony\Contracts\EventDispatcher\Event;

final class EmailReplyEvent extends Event
{
    private readonly ?Email $email;

    public function __construct(
        private readonly Stat $stat,
    ) {
        $this->email = $stat->getEmail();
    }

    /**
     * Returns the Email entity.
     */
    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function getStat(): Stat
    {
        return $this->stat;
    }
}
