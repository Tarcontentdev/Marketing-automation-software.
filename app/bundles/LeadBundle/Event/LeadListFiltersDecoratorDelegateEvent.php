<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\FilterDecoratorInterface;

final class LeadListFiltersDecoratorDelegateEvent extends CommonEvent
{
    private ?FilterDecoratorInterface $decorator = null;

    public function __construct(
        private readonly ContactSegmentFilterCrate $crate,
    ) {
    }

    public function getDecorator(): ?FilterDecoratorInterface
    {
        return $this->decorator;
    }

    public function setDecorator(FilterDecoratorInterface $decorator): self
    {
        $this->decorator = $decorator;

        return $this;
    }

    public function getCrate(): ContactSegmentFilterCrate
    {
        return $this->crate;
    }
}
