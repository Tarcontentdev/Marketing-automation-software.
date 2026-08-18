<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\Event;

use MailVotech\LeadBundle\Entity\LeadField;
use Symfony\Contracts\EventDispatcher\Event;

final class DeleteColumnBackgroundEvent extends Event
{
    public function __construct(
        private readonly LeadField $leadField,
    ) {
    }

    public function getLeadField(): LeadField
    {
        return $this->leadField;
    }
}
