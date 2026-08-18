<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Query\Filter;

use MailVotech\LeadBundle\Segment\ContactSegmentFilter;
use MailVotech\LeadBundle\Segment\Query\QueryBuilder;

interface FilterQueryBuilderInterface
{
    /**
     * @return QueryBuilder
     */
    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter);

    /**
     * @return string returns the service id in the DIC container
     */
    public static function getServiceId();
}
