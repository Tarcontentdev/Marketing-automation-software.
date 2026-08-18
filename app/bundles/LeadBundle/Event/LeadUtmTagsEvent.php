<?php

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Lead;

final class LeadUtmTagsEvent extends CommonEvent
{
    /**
     * @param mixed[] $utmtags
     */
    public function __construct(
        Lead $lead,
        private readonly array $utmtags,
    ) {
        $this->entity  = $lead;
    }

    public function getLead(): Lead
    {
        return $this->entity;
    }

    /**
     * Returns the new points.
     *
     * @return mixed[]
     */
    public function getUtmTags(): array
    {
        return $this->utmtags;
    }
}
