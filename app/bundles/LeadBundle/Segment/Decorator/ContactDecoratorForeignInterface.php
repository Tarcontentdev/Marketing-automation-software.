<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;

interface ContactDecoratorForeignInterface
{
    /**
     * Returns the name of a foreign contact column used in JOIN condition (usually contact_id or lead_id).
     */
    public function getForeignContactColumn(ContactSegmentFilterCrate $contactSegmentFilterCrate): string;
}
