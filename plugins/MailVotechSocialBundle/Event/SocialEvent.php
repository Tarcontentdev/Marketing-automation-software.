<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotechPlugin\MailVotechSocialBundle\Entity\Monitoring;

final class SocialEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Monitoring $monitoring, $isNew = false)
    {
        $this->entity = $monitoring;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Monitoring entity.
     *
     * @return Monitoring
     */
    public function getMonitoring()
    {
        return $this->entity;
    }

    /**
     * Sets the Monitoring entity.
     */
    public function setMonitoring(Monitoring $monitoring): void
    {
        $this->entity = $monitoring;
    }
}
