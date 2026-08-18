<?php

namespace MailVotech\ReportBundle\Model;

use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\ReportBundle\Crate\ReportDataResult;
use MailVotech\ReportBundle\Entity\Scheduler;
use MailVotech\ReportBundle\Exception\FileIOException;

class ReportFileWriter
{
    public function __construct(
        private readonly CsvExporter $csvExporter,
        private readonly ExportHandler $exportHandler,
    ) {
    }

    /**
     * @throws FileIOException
     */
    public function writeReportData(Scheduler $scheduler, ReportDataResult $reportDataResult, ReportExportOptions $reportExportOptions): void
    {
        $fileName = $this->getFileName($scheduler);
        $handler  = $this->exportHandler->getHandler($fileName);
        $this->csvExporter->export($reportDataResult, $handler, $reportExportOptions->getPage());
        $this->exportHandler->closeHandler($handler);
    }

    public function clear(Scheduler $scheduler): void
    {
        $fileName = $this->getFileName($scheduler);
        $this->exportHandler->removeFile($fileName);
    }

    /**
     * @throws FileIOException
     */
    public function getFilePath(Scheduler $scheduler): string
    {
        $fileName = $this->getFileName($scheduler);

        return $this->exportHandler->getPath($fileName);
    }

    private function getFileName(Scheduler $scheduler): string
    {
        $date       = $scheduler->getScheduleDate();
        $dateString = $date->format('Y-m-d');
        $reportName = $scheduler->getReport()->getName();

        return $dateString.'_'.InputHelper::alphanum($reportName, false, '-');
    }
}
