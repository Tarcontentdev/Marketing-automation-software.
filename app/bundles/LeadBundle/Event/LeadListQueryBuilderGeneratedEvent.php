<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Segment\Query\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

final class LeadListQueryBuilderGeneratedEvent extends Event
{
    public function __construct(
        private readonly LeadList $segment,
        private readonly QueryBuilder $queryBuilder,
    ) {
    }

    public function getSegment(): LeadList
    {
        return $this->segment;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
