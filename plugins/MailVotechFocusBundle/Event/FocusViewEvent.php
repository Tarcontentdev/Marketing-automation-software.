<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Event;

use MailVotechPlugin\MailVotechFocusBundle\Entity\Stat;
use Symfony\Contracts\EventDispatcher\Event;

final class FocusViewEvent extends Event
{
    public function __construct(
        private readonly Stat $stat,
    ) {
    }

    public function getStat(): Stat
    {
        return $this->stat;
    }
}
