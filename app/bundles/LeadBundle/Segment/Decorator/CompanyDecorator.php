<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment\Decorator;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Query\Filter\ComplexRelationValueFilterQueryBuilder;

class CompanyDecorator extends BaseDecorator
{
    public function getRelationJoinTable(): string
    {
        return MAILVOTECH_TABLE_PREFIX.'companies_leads';
    }

    public function getRelationJoinTableField(): string
    {
        return 'company_id';
    }

    public function getQueryType(ContactSegmentFilterCrate $contactSegmentFilterCrate): string
    {
        return ComplexRelationValueFilterQueryBuilder::getServiceId();
    }
}
