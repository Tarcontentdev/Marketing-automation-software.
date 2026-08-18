<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tracker\Service\DeviceCreatorService;

use DeviceDetector\DeviceDetector;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadDevice;

/**
 * Interface DeviceCreatorServiceInterface.
 */
interface DeviceCreatorServiceInterface
{
    /**
     * @return LeadDevice|null Null is returned if device can't be detected
     */
    public function getCurrentFromDetector(DeviceDetector $deviceDetector, Lead $assignedLead);
}
