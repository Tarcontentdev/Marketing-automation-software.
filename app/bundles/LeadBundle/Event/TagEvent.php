<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Tag;

final class TagEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Tag $tag, $isNew = false)
    {
        $this->entity = $tag;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Tag entity.
     *
     * @return Tag
     */
    public function getTag()
    {
        return $this->entity;
    }
}
