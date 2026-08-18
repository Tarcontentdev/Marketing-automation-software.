<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tracker\Factory\DeviceDetectorFactory;

use DeviceDetector\DeviceDetector;

/**
 * Interface DeviceDetectorFactoryInterface.
 */
interface DeviceDetectorFactoryInterface
{
    /**
     * @param string $userAgent
     *
     * @return DeviceDetector
     */
    public function create($userAgent);
}
