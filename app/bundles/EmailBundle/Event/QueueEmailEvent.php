<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Event;

use MailVotech\EmailBundle\Mailer\Message\MailVotechMessage;
use Symfony\Contracts\EventDispatcher\Event;

final class QueueEmailEvent extends Event
{
    private bool $retry = false;

    public function __construct(
        private readonly MailVotechMessage $message,
    ) {
    }

    public function getMessage(): MailVotechMessage
    {
        return $this->message;
    }

    /**
     * Sets whether the sending of the message should be tried again.
     */
    public function tryAgain(): void
    {
        $this->retry = true;
    }

    public function shouldTryAgain(): bool
    {
        return $this->retry;
    }
}
