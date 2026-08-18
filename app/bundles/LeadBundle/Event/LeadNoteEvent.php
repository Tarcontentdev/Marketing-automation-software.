<?php

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadNote;

final class LeadNoteEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(LeadNote $note, $isNew = false)
    {
        $this->entity = $note;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the LeadNote entity.
     *
     * @return LeadNote
     */
    public function getNote()
    {
        return $this->entity;
    }

    /**
     * Sets the LeadNote entity.
     */
    public function setLeadNote(LeadNote $note): void
    {
        $this->entity = $note;
    }

    /**
     * Returns the Lead.
     *
     * @return Lead
     */
    public function getLead()
    {
        return $this->entity->getLead();
    }
}
