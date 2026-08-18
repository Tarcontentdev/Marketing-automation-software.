<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\PointBundle\Entity\Trigger;

final class TriggerEvent extends CommonEvent
{
    /**
     * @var Trigger
     */
    protected $entity;

    /**
     * @param bool $isNew
     */
    public function __construct(
        Trigger &$trigger,
        protected $isNew = false,
    ) {
        $this->entity = &$trigger;
    }

    /**
     * @return Trigger
     */
    public function getTrigger()
    {
        return $this->entity;
    }

    public function setTrigger(Trigger $trigger): void
    {
        $this->entity = $trigger;
    }
}
