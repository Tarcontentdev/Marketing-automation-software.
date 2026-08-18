<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\SmsBundle\Sms\TransportInterface;

final class ArrayTransport implements TransportInterface
{
    /**
     * @var array<array{'contact': Lead, 'content': string}>
     */
    public array $smses = [];

    /**
     * @var array<array{'contact': Lead, 'content': string}>
     */
    public array $mmses = [];

    public function sendSms(Lead $lead, $content): bool
    {
        $this->smses[] = ['contact' => $lead, 'content' => $content];

        return true;
    }
}
