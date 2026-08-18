<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;

class DateDecorator extends CustomMappedDecorator
{
    /**
     * @throws \Exception
     */
    public function getParameterValue(ContactSegmentFilterCrate $contactSegmentFilterCrate): mixed
    {
        throw new \Exception('Instance of Date option needs to implement this function');
    }
}
