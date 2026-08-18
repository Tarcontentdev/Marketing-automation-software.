<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\CoreBundle\Event\DependencyErrorEventInterface;
use MailVotech\CoreBundle\Event\DependencyErrorEventTrait;
use MailVotech\LeadBundle\Entity\LeadList;

class LeadListEvent extends CommonEvent implements DependencyErrorEventInterface
{
    use DependencyErrorEventTrait;

    /**
     * @param bool $isNew
     */
    public function __construct(LeadList $list, $isNew = false)
    {
        $this->entity = $list;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the List entity.
     *
     * @return LeadList
     */
    public function getList()
    {
        return $this->entity;
    }

    /**
     * Sets the List entity.
     */
    public function setList(LeadList $list): void
    {
        $this->entity = $list;
    }
}
