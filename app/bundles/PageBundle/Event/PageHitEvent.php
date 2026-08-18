<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\Page;

class PageHitEvent extends CommonEvent
{
    protected ?Page $page = null;

    /**
     * @param mixed[] $clickthroughData
     * @param bool    $unique
     */
    public function __construct(
        Hit $hit,
        protected $request,
        protected $code,
        protected $clickthroughData = [],
        protected $unique = false,
    ) {
        $this->entity           = $hit;
        $this->page             = $hit->getPage();
    }

    /**
     * Returns the Page entity.
     */
    public function getPage(): ?Page
    {
        return $this->page;
    }

    /**
     * Get page request.
     *
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get HTML code.
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Hit
     */
    public function getHit()
    {
        return $this->entity;
    }

    /**
     * @return mixed
     */
    public function getClickthroughData()
    {
        return $this->clickthroughData;
    }

    /**
     * Returns if this page hit is unique.
     *
     * @return bool
     */
    public function isUnique()
    {
        return $this->unique;
    }
}
