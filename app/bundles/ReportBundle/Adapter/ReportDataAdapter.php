<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Adapter;

use MailVotech\ReportBundle\Crate\ReportDataResult;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Model\ReportExportOptions;
use MailVotech\ReportBundle\Model\ReportModel;

class ReportDataAdapter
{
    public function __construct(
        private readonly ReportModel $reportModel,
    ) {
    }

    public function getReportData(Report $report, ReportExportOptions $reportExportOptions): ReportDataResult
    {
        $options                    = [];
        $options['paginate']        = true;
        $options['limit']           = $reportExportOptions->getBatchSize();
        $options['ignoreGraphData'] = true;
        $options['page']            = $reportExportOptions->getPage();
        $options['dateTo']          = $reportExportOptions->getDateTo();
        $options['dateFrom']        = $reportExportOptions->getDateFrom();

        $data = $this->reportModel->getReportData($report, null, $options);

        return new ReportDataResult($data);
    }
}
