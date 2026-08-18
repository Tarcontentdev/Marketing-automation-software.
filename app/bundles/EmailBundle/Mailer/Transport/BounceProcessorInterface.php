<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Mailer\Transport;

use MailVotech\EmailBundle\MonitoredEmail\Exception\BounceNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;

/**
 * Interface InterfaceBounceProcessor.
 */
interface BounceProcessorInterface
{
    /**
     * Get the email address that bounced.
     *
     * @throws BounceNotFound
     */
    public function processBounce(Message $message): BouncedEmail;
}
