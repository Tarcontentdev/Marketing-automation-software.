<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Sms;

use MailVotech\LeadBundle\Entity\Lead;

interface TransportInterface
{
    /**
     * @param string $content
     *
     * @return bool
     */
    public function sendSms(Lead $lead, $content);
}
