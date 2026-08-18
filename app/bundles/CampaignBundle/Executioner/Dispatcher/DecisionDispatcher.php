<?php

namespace MailVotech\CampaignBundle\Executioner\Dispatcher;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\DecisionEvent;
use MailVotech\CampaignBundle\Event\DecisionResultsEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\DecisionAccessor;
use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class DecisionDispatcher
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LegacyEventDispatcher $legacyDispatcher,
    ) {
    }

    /**
     * @param mixed $passthrough
     */
    public function dispatchRealTimeEvent(DecisionAccessor $config, LeadEventLog $log, $passthrough): DecisionEvent
    {
        $event = new DecisionEvent($config, $log, $passthrough);
        $this->dispatcher->dispatch($event, $config->getEventName());

        return $event;
    }

    public function dispatchEvaluationEvent(DecisionAccessor $config, LeadEventLog $log): DecisionEvent
    {
        $event = new DecisionEvent($config, $log);

        $this->dispatcher->dispatch($event, CampaignEvents::ON_EVENT_DECISION_EVALUATION);
        $this->legacyDispatcher->dispatchDecisionEvent($event);

        return $event;
    }

    public function dispatchDecisionResultsEvent(DecisionAccessor $config, ArrayCollection $logs, EvaluatedContacts $evaluatedContacts): void
    {
        if (!$logs->count()) {
            return;
        }

        $this->dispatcher->dispatch(
            new DecisionResultsEvent($config, $logs, $evaluatedContacts),
            CampaignEvents::ON_EVENT_DECISION_EVALUATION_RESULTS
        );
    }
}
