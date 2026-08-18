<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\LeadDevice;

final class LeadDeviceEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(LeadDevice &$device, $isNew = false)
    {
        $this->entity = &$device;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the LeadDevice entity.
     *
     * @return LeadDevice
     */
    public function getDevice()
    {
        return $this->entity;
    }

    /**
     * Sets the LeadDevice entity.
     */
    public function setDevice(LeadDevice $device): void
    {
        $this->entity = $device;
    }
}
