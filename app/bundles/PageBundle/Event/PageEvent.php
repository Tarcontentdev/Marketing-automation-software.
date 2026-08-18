<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\PageBundle\Entity\Page;

final class PageEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Page $page, $isNew = false)
    {
        $this->entity = $page;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Page entity.
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->entity;
    }

    /**
     * Sets the Page entity.
     */
    public function setPage(Page $page): void
    {
        $this->entity = $page;
    }
}
