<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Lead;

final class LeadEvent extends CommonEvent
{
    private bool $alreadyProcessedInBatch = false;

    public function __construct(
        Lead $lead,
        bool $isNew = false,
    ) {
        $this->entity = $lead;
        $this->isNew  = $isNew;
    }

    public function getLead(): Lead
    {
        return $this->entity;
    }

    public function setLead(Lead $lead): void
    {
        $this->entity = $lead;
    }

    public function isAlreadyProcessedInBatch(): bool
    {
        return $this->alreadyProcessedInBatch;
    }

    public function setAlreadyProcessedInBatch(bool $alreadyProcessedInBatch): void
    {
        $this->alreadyProcessedInBatch = $alreadyProcessedInBatch;
    }
}
