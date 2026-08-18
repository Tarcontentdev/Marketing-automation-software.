<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\CoreBundle\Event\DependencyErrorEventInterface;
use MailVotech\CoreBundle\Event\DependencyErrorEventTrait;
use MailVotech\LeadBundle\Entity\LeadField;

final class LeadFieldEvent extends CommonEvent implements DependencyErrorEventInterface
{
    use DependencyErrorEventTrait;

    /**
     * @param bool $isNew
     */
    public function __construct(LeadField &$field, $isNew = false)
    {
        $this->entity = &$field;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Field entity.
     *
     * @return LeadField
     */
    public function getField()
    {
        return $this->entity;
    }

    /**
     * Sets the LeadField entity.
     */
    public function setField(LeadField $field): void
    {
        $this->entity = $field;
    }
}
