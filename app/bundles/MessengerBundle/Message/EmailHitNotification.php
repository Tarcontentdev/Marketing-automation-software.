<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\Message;

use MailVotech\MessengerBundle\Message\Traits\MessageRequestTrait;
use Symfony\Component\HttpFoundation\Request;

final class EmailHitNotification
{
    use MessageRequestTrait;

    public function __construct(
        private string $statId,
        private Request $request,
        ?\DateTimeInterface $eventTime = null,
    ) {
        $this->setEventTime($eventTime ?? new \DateTimeImmutable());
    }

    public function getStatId(): string
    {
        return $this->statId;
    }
}
