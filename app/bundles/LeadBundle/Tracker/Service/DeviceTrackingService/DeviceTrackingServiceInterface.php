<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tracker\Service\DeviceTrackingService;

use MailVotech\LeadBundle\Entity\LeadDevice;

/**
 * Interface DeviceTrackingServiceInterface.
 */
interface DeviceTrackingServiceInterface
{
    /**
     * @return bool
     */
    public function isTracked();

    /**
     * @return LeadDevice|null
     */
    public function getTrackedDevice();

    /**
     * @param bool $replaceExistingTracking
     *
     * @return LeadDevice
     */
    public function trackCurrentDevice(LeadDevice $device, $replaceExistingTracking = false);

    public function clearTrackingCookies();

    /**
     * Resets cache.
     */
    public function reset(): void;
}
