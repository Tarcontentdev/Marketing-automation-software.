<?php

namespace MailVotech\LeadBundle\Segment\Stat;

use MailVotech\LeadBundle\Entity\LeadListRepository;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\LeadBundle\Segment\Stat\ChartQuery\SegmentContactsLineChartQuery;

class SegmentChartQueryFactory
{
    public function __construct(
        private readonly LeadListRepository $leadListRepository,
    ) {
    }

    public function getContactsTotal(SegmentContactsLineChartQuery $query, ListModel $listModel): array
    {
        $total = $this->leadListRepository->getLeadCount($query->getSegmentId());

        return $query->getTotalStats($total);
    }

    public function getContactsAdded(SegmentContactsLineChartQuery $query): array
    {
        return $query->getAddedEventLogStats();
    }

    public function getContactsRemoved(SegmentContactsLineChartQuery $query): ?array
    {
        return $query->getRemovedEventLogStats();
    }
}
