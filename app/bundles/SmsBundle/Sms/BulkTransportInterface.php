<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Sms;

use MailVotech\SmsBundle\Collection\RecipientCollection;
use MailVotech\SmsBundle\Helper\DTO\SmsRecipientDTO;

interface BulkTransportInterface extends TransportInterface
{
    /**
     * @param RecipientCollection<SmsRecipientDTO> $collection
     *
     * @return RecipientCollection<SmsRecipientDTO>
     */
    public function sendBatchSms(RecipientCollection $collection, string $content): RecipientCollection;
}
