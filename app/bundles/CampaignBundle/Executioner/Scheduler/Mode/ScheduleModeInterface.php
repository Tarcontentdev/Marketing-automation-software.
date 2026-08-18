<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Executioner\Scheduler\Mode;

use MailVotech\CampaignBundle\Entity\Event;

interface ScheduleModeInterface
{
    public function getExecutionDateTime(Event $event, \DateTimeInterface $now, \DateTimeInterface $comparedToDateTime): \DateTimeInterface;
}
