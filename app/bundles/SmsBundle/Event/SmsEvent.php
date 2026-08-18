<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\SmsBundle\Entity\Sms;

final class SmsEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Sms $sms, $isNew = false)
    {
        $this->entity = $sms;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Sms entity.
     *
     * @return Sms
     */
    public function getSms()
    {
        return $this->entity;
    }

    /**
     * Sets the Sms entity.
     */
    public function setSms(Sms $sms): void
    {
        $this->entity = $sms;
    }
}
