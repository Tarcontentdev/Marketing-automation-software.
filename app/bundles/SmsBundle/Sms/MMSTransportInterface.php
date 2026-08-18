<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Sms;

use MailVotech\LeadBundle\Entity\Lead;

interface MMSTransportInterface
{
    /**
     * @param array<mixed> $media
     */
    public function sendMms(Lead $lead, string $content, array $media): bool|string;
}
