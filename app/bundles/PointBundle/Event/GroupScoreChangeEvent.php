<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Event;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PointBundle\Entity\Group;
use MailVotech\PointBundle\Entity\GroupContactScore;

final readonly class GroupScoreChangeEvent
{
    public function __construct(
        private GroupContactScore $groupContactScore,
        private int $oldScore,
        private int $newScore,
    ) {
    }

    public function getGroupContactScore(): GroupContactScore
    {
        return $this->groupContactScore;
    }

    public function getContact(): Lead
    {
        return $this->groupContactScore->getContact();
    }

    public function getGroup(): Group
    {
        return $this->groupContactScore->getGroup();
    }

    public function getNewScore(): int
    {
        return $this->newScore;
    }

    public function getOldScore(): int
    {
        return $this->oldScore;
    }
}
