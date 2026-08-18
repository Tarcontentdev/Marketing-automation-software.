<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\DynamicContentBundle\Entity\DynamicContent;

final class DynamicContentEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(DynamicContent $entity, $isNew = false)
    {
        $this->entity = $entity;
        $this->isNew  = $isNew;
    }

    /**
     * @return DynamicContent
     */
    public function getDynamicContent()
    {
        return $this->entity;
    }

    public function setDynamicContent(DynamicContent $entity): void
    {
        $this->entity = $entity;
    }
}
