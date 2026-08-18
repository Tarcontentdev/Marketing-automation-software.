<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tracker\Service\ContactTrackingService;

use MailVotech\LeadBundle\Entity\Lead;

/**
 * Interface ContactTrackingInterface.
 */
interface ContactTrackingServiceInterface
{
    /**
     * Return current tracked Lead.
     *
     * @return Lead|null
     */
    public function getTrackedLead();

    /**
     * @return string|null Unique identifier
     */
    public function getTrackedIdentifier();
}
