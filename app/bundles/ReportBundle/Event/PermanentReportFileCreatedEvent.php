<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Event;

use MailVotech\ReportBundle\Entity\Report;

final class PermanentReportFileCreatedEvent extends AbstractReportEvent
{
    public function __construct(Report $report)
    {
        $this->report = $report;
    }
}
