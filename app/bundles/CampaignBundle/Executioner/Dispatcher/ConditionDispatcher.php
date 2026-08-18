<?php

namespace MailVotech\CampaignBundle\Executioner\Dispatcher;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\ConditionEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ConditionAccessor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class ConditionDispatcher
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function dispatchEvent(ConditionAccessor $config, LeadEventLog $log): ConditionEvent
    {
        $event = new ConditionEvent($config, $log);
        $this->dispatcher->dispatch($event, $config->getEventName());
        $this->dispatcher->dispatch($event, CampaignEvents::ON_EVENT_CONDITION_EVALUATION);

        return $event;
    }
}
