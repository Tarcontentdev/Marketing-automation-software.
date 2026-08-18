<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Event;

use MailVotech\CampaignBundle\DTO\EventPreviewStatDto;
use MailVotech\CampaignBundle\Entity\Event;

final class EventPreview
{
    /**
     * @var array<string, EventPreviewStatDto>
     */
    public array $eventStats = [];

    public function __construct(
        public Event $event,
    ) {
    }

    public function isType(string $type): bool
    {
        return $this->event->getType() === $type;
    }

    public function isCampaignRestartAllowed(): bool
    {
        return $this->event->getCampaign()->getAllowRestart();
    }

    public function addEventStat(string $key, mixed $value, ?string $tooltip = null): void
    {
        $this->eventStats[$key] = new EventPreviewStatDto($value, $tooltip);
    }
}
