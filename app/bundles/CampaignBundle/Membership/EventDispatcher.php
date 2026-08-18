<?php

namespace MailVotech\CampaignBundle\Membership;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Event\CampaignLeadChangeEvent;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @param string $action
     */
    public function dispatchMembershipChange(Lead $contact, Campaign $campaign, $action): void
    {
        $this->dispatcher->dispatch(
            new CampaignLeadChangeEvent($campaign, $contact, $action),
            CampaignEvents::CAMPAIGN_ON_LEADCHANGE
        );
    }

    public function dispatchBatchMembershipChange(array $contacts, Campaign $campaign, $action): void
    {
        $this->dispatcher->dispatch(
            new CampaignLeadChangeEvent($campaign, $contacts, $action),
            CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE
        );
    }
}
