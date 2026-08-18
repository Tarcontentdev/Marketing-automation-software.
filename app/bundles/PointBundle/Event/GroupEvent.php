<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Event;

use MailVotech\PointBundle\Entity\Group;

final class GroupEvent
{
    public function __construct(
        private Group $entity,
    ) {
    }

    public function getGroup(): Group
    {
        return $this->entity;
    }

    public function setGroup(Group $group): void
    {
        $this->entity = $group;
    }
}
