<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\MonitoredEmail\Processor;

use MailVotech\EmailBundle\MonitoredEmail\Message;

interface ProcessorInterface
{
    /**
     * Process the message.
     *
     * @return bool|void
     */
    public function process(Message $message);
}
