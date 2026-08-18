<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Mailer\Transport;

use Symfony\Component\Mime\Email;

trait TokenTransportTrait
{
    public function getBatchRecipientCount(Email $message, int $toBeAdded = 1, string $type = 'to'): int
    {
        return count($message->getTo()) + count($message->getCc()) + count($message->getBcc()) + $toBeAdded;
    }
}
