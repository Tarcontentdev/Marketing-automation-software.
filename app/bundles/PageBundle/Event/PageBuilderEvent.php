<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Event;

use MailVotech\CoreBundle\Event\BuilderEvent;
use MailVotech\PageBundle\Entity\Page;

final class PageBuilderEvent extends BuilderEvent
{
    /**
     * @return Page|null
     */
    public function getPage()
    {
        return $this->entity;
    }
}
