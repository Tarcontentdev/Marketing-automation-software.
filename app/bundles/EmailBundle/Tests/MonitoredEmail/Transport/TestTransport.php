<?php

namespace MailVotech\EmailBundle\Tests\MonitoredEmail\Transport;

use MailVotech\EmailBundle\Mailer\Transport\BounceProcessorInterface;
use MailVotech\EmailBundle\Mailer\Transport\UnsubscriptionProcessorInterface;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

final readonly class TestTransport implements TransportInterface, BounceProcessorInterface, UnsubscriptionProcessorInterface
{
    private NullTransport $nullTransport;

    public function __construct()
    {
        $this->nullTransport = new NullTransport();
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        return $this->nullTransport->send($message, $envelope);
    }

    public function __toString(): string
    {
        return (string) $this->nullTransport;
    }

    public function processBounce(Message $message): BouncedEmail
    {
        return new BouncedEmail();
    }

    public function processUnsubscription(Message $message): UnsubscribedEmail
    {
        return new UnsubscribedEmail('contact@email.com', 'test+unsubscribe_123abc@test.com');
    }
}
