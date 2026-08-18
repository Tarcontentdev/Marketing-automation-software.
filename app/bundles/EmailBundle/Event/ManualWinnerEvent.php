<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Event;

use MailVotech\EmailBundle\Entity\Email;
use Symfony\Contracts\EventDispatcher\Event;

final class ManualWinnerEvent extends Event
{
    public function __construct(
        private readonly Email $email,
    ) {
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
