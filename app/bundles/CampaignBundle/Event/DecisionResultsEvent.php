<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;
use Symfony\Contracts\EventDispatcher\Event;

final class DecisionResultsEvent extends Event
{
    /**
     * @param ArrayCollection<int, LeadEventLog> $eventLogs
     */
    public function __construct(
        private readonly AbstractEventAccessor $eventConfig,
        private readonly ArrayCollection $eventLogs,
        private readonly EvaluatedContacts $evaluatedContacts,
    ) {
    }

    public function getEventConfig(): AbstractEventAccessor
    {
        return $this->eventConfig;
    }

    /**
     * @return ArrayCollection<int, LeadEventLog>
     */
    public function getLogs(): ArrayCollection
    {
        return $this->eventLogs;
    }

    public function getEvaluatedContacts(): EvaluatedContacts
    {
        return $this->evaluatedContacts;
    }
}
