<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Adapter;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\ReportBundle\Adapter\ReportDataAdapter;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Model\ReportExportOptions;
use MailVotech\ReportBundle\Model\ReportModel;
use MailVotech\ReportBundle\Tests\Fixtures;

final class ReportDataAdapterTest extends \PHPUnit\Framework\TestCase
{
    public function testNoEmailsProvided(): void
    {
        $reportModelMock = $this->createMock(ReportModel::class);

        $coreParametersHelperMock = $this->createMock(CoreParametersHelper::class);

        $coreParametersHelperMock->expects($this->once())
            ->method('get')
            ->with('report_export_batch_size')
            ->willReturn(11);

        $reportDataAdapter = new ReportDataAdapter($reportModelMock);

        $report              = new Report();
        $reportExportOptions = new ReportExportOptions($coreParametersHelperMock);

        $options = [
            'paginate'        => true,
            'limit'           => 11,
            'ignoreGraphData' => true,
            'page'            => 1,
            'dateTo'          => null,
            'dateFrom'        => null,
        ];

        $reportModelMock->expects($this->once())
            ->method('getReportData')
            ->with($report, null, $options)
            ->willReturn(Fixtures::getValidReportResult());

        $result = $reportDataAdapter->getReportData($report, $reportExportOptions);

        $this->assertSame(Fixtures::getValidReportData(), $result->getData());
        $this->assertSame(Fixtures::getValidReportHeaders(), $result->getHeaders());
        $this->assertSame(Fixtures::getValidReportTotalResult(), $result->getTotalResults());
        $this->assertSame(Fixtures::getStringType(), $result->getType('city'));
        $this->assertSame(Fixtures::getDateType(), $result->getType('date_identified'));
        $this->assertSame(Fixtures::getEmailType(), $result->getType('email'));
    }
}
