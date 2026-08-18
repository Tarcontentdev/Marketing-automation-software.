<?php

namespace MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscription;

use MailVotech\EmailBundle\MonitoredEmail\Exception\UnsubscriptionNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;

final readonly class Parser
{
    public function __construct(
        private Message $message,
    ) {
    }

    /**
     * @throws UnsubscriptionNotFound
     */
    public function parse(): UnsubscribedEmail
    {
        $unsubscriptionEmail = null;
        foreach ($this->message->to as $to => $name) {
            if (str_contains($to, '+unsubscribe')) {
                $unsubscriptionEmail = $to;

                break;
            }
        }

        if (!$unsubscriptionEmail) {
            throw new UnsubscriptionNotFound();
        }

        return new UnsubscribedEmail($this->message->fromAddress, $unsubscriptionEmail);
    }
}
