<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\PointBundle\Entity\Point;

final class PointEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Point &$point, $isNew = false)
    {
        $this->entity = &$point;
        $this->isNew  = $isNew;
    }

    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->entity;
    }

    public function setPoint(Point $point): void
    {
        $this->entity = $point;
    }
}
