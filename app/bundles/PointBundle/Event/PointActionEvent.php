<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PointBundle\Entity\Point;

final class PointActionEvent extends CommonEvent
{
    public function __construct(
        private Point $point,
        private Lead $lead,
    ) {
    }

    public function getPoint(): Point
    {
        return $this->point;
    }

    public function setPoint(Point $point): void
    {
        $this->point = $point;
    }

    public function getLead(): Lead
    {
        return $this->lead;
    }

    public function setLead(Lead $lead): void
    {
        $this->lead = $lead;
    }
}
