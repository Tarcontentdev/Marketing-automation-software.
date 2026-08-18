<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Mailer\Transport;

use MailVotech\EmailBundle\MonitoredEmail\Exception\UnsubscriptionNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail;

/**
 * Interface InterfaceUnsubscriptionProcessor.
 */
interface UnsubscriptionProcessorInterface
{
    /**
     * Get the email address that unsubscribed.
     *
     * @throws UnsubscriptionNotFound
     */
    public function processUnsubscription(Message $message): UnsubscribedEmail;
}
