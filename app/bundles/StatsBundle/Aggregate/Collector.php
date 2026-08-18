<?php

namespace MailVotech\StatsBundle\Aggregate;

use MailVotech\StatsBundle\Aggregate\Collection\StatCollection;
use MailVotech\StatsBundle\Event\AggregateStatRequestEvent;
use MailVotech\StatsBundle\Event\Options\FetchOptions;
use MailVotech\StatsBundle\StatEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class Collector
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param string $statName
     */
    public function fetchStats($statName, \DateTime $fromDateTime, \DateTime $toDateTime, ?FetchOptions $fetchOptions = null): StatCollection
    {
        if (null === $fetchOptions) {
            $fetchOptions = new FetchOptions();
        }

        $event = new AggregateStatRequestEvent($statName, $fromDateTime, $toDateTime, $fetchOptions);

        $this->eventDispatcher->dispatch($event, StatEvents::AGGREGATE_STAT_REQUEST);

        return $event->getStatCollection();
    }
}
