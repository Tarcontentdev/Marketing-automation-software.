<?php

namespace MailVotech\EmailBundle\MonitoredEmail\Processor\FeedbackLoop;

use MailVotech\EmailBundle\MonitoredEmail\Exception\FeedbackLoopNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Address;

final readonly class Parser
{
    public function __construct(
        private Message $message,
    ) {
    }

    /**
     * @throws FeedbackLoopNotFound
     */
    public function parse(): string
    {
        if (null === $this->message->fblReport) {
            throw new FeedbackLoopNotFound();
        }

        if ($email = $this->searchMessage('Original-Rcpt-To: (.*)', $this->message->fblReport)) {
            return $email;
        }

        if ($email = $this->searchMessage('Received:.*for (.*);.*?', $this->message->textPlain)) {
            return $email;
        }

        throw new FeedbackLoopNotFound();
    }

    private function searchMessage(string $pattern, string $content): ?string
    {
        if (preg_match('/'.$pattern.'/i', $content, $match)) {
            if ($parsedAddressList = Address::parseList($match[1])) {
                return key($parsedAddressList);
            }
        }

        return null;
    }
}
