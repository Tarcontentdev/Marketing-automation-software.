<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Event;

final class DeleteEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @param string[] $eventIds
     */
    public function __construct(
        private readonly array $eventIds,
    ) {
    }

    /**
     * @return string[]
     */
    public function getEventIds(): array
    {
        return $this->eventIds;
    }
}
