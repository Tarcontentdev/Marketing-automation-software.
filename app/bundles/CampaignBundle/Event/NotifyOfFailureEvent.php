<?php

namespace MailVotech\CampaignBundle\Event;

use MailVotech\CampaignBundle\Entity\Event as CampaignEvent;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Contracts\EventDispatcher\Event;

final class NotifyOfFailureEvent extends Event
{
    public function __construct(
        private readonly Lead $lead,
        private readonly CampaignEvent $failedEvent,
    ) {
    }

    public function getLead(): Lead
    {
        return $this->lead;
    }

    public function getFailedEvent(): CampaignEvent
    {
        return $this->failedEvent;
    }
}
