<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Collection;

use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Exception\RecipientNotFoundException;
use MailVotech\SmsBundle\Helper\DTO\SmsRecipientDTO;

final class RecipientCollection extends \ArrayIterator
{
    /**
     * @param array<SmsRecipientDTO> $recipients
     */
    public function __construct(
        private readonly Sms $sms,
        array $recipients = [],
    ) {
        parent::__construct($recipients);
    }

    /**
     * @return array<SmsRecipientDTO>
     */
    public function toChoices(): array
    {
        $choices = [];

        /** @var SmsRecipientDTO $recipient */
        foreach ($this as $recipient) {
            $choices[$recipient->getKey()] = $recipient;
        }

        return $choices;
    }

    /**
     * @throws RecipientNotFoundException
     */
    public function getFieldByKey(int $key): SmsRecipientDTO
    {
        /** @var SmsRecipientDTO $recipient */
        foreach ($this as $recipient) {
            if ($key === $recipient->getKey()) {
                return $recipient;
            }
        }

        throw new RecipientNotFoundException("Recipient with key {$key} was not found.");
    }

    public function getSms(): Sms
    {
        return $this->sms;
    }
}
