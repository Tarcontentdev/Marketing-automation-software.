<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\Tests\EventListener;

use MailVotech\AssetBundle\Entity\DownloadRepository;
use MailVotech\AssetBundle\EventListener\ReportSubscriber;
use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Model\CompanyReportData;
use MailVotech\LeadBundle\Report\DncReportService;
use MailVotech\LeadBundle\Segment\Query\QueryBuilder;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Event\ReportBuilderEvent;
use MailVotech\ReportBundle\Event\ReportGeneratorEvent;
use MailVotech\ReportBundle\Helper\ReportHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ReportSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private ChannelListHelper $channelListHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&QueryBuilder
     */
    private \PHPUnit\Framework\MockObject\MockObject $queryBuilder;

    private ReportHelper $reportHelper;

    protected function setUp(): void
    {
        $this->queryBuilder        = $this->createMock(QueryBuilder::class);
        $this->channelListHelper   = new ChannelListHelper($this->createStub(EventDispatcherInterface::class), $this->createStub(Translator::class));
        $this->reportHelper        = new ReportHelper($this->createStub(EventDispatcherInterface::class));
    }

    public function testOnReportBuilderWithUnknownContext(): void
    {
        $companyReportData = new class() extends CompanyReportData {
            public function __construct()
            {
            }
        };

        $downloadRepository = new class() extends DownloadRepository {
            public function __construct()
            {
            }
        };

        $event = new class() extends ReportBuilderEvent {
            public function __construct()
            {
                $this->context = 'unicorn';
            }
        };

        $reportSubscriber = new ReportSubscriber($companyReportData, $downloadRepository, $this->createStub(DncReportService::class));

        $reportSubscriber->onReportBuilder($event);

        $this->assertSame([], $event->getTables());
    }

    public function testOnReportBuilderWithAssetDownloadContext(): void
    {
        $companyReportData = new class() extends CompanyReportData {
            public function __construct()
            {
            }

            /**
             * @return array<mixed>
             */
            public function getCompanyData(): array
            {
                return [];
            }
        };

        $downloadRepository = new class() extends DownloadRepository {
            public function __construct()
            {
            }
        };

        $event = new ReportBuilderEvent($this->createTranslatorMock(), $this->channelListHelper, ReportSubscriber::CONTEXT_ASSET_DOWNLOAD, [], $this->reportHelper);

        $reportSubscriber = new ReportSubscriber($companyReportData, $downloadRepository, $this->createStub(DncReportService::class));

        $reportSubscriber->onReportBuilder($event);

        $this->assertSame([
            'alias' => 'download_count',
            'label' => '[trans]mailvotech.asset.report.download_count[/trans]',
            'type'  => 'int',
        ], $event->getTables()['assets']['columns']['a.download_count']);

        $this->assertSame([
            'alias' => 'unique_download_count',
            'label' => '[trans]mailvotech.asset.report.unique_download_count[/trans]',
            'type'  => 'int',
        ], $event->getTables()['assets']['columns']['a.unique_download_count']);

        $this->assertSame([
            'alias'   => 'download_count',
            'label'   => '[trans]mailvotech.asset.report.download_count[/trans]',
            'type'    => 'int',
            'formula' => 'COUNT(ad.id)',
        ], $event->getTables()['asset.downloads']['columns']['a.download_count']);

        $this->assertSame([
            'alias'   => 'unique_download_count',
            'label'   => '[trans]mailvotech.asset.report.unique_download_count[/trans]',
            'type'    => 'int',
            'formula' => 'COUNT(DISTINCT ad.lead_id)',
        ], $event->getTables()['asset.downloads']['columns']['a.unique_download_count']);
    }

    private function createTranslatorMock(): TranslatorInterface
    {
        return new class() implements TranslatorInterface {
            /**
             * @param array<int|string> $parameters
             */
            public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
            {
                return '[trans]'.$id.'[/trans]';
            }

            public function getLocale(): string
            {
                return 'en';
            }
        };
    }

    public function testGroupByDefaultConfigured(): void
    {
        $report             = new Report();
        $report->setSource(ReportSubscriber::CONTEXT_ASSET_DOWNLOAD);
        $event              = new ReportGeneratorEvent($report, [], $this->queryBuilder, $this->channelListHelper);
        $subscriber         = new ReportSubscriber($this->createStub(CompanyReportData::class), $this->createStub(DownloadRepository::class), $this->createStub(DncReportService::class));
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('groupBy')
            ->with('ad.id');

        $this->assertFalse($event->hasGroupBy());

        $subscriber->onReportGenerate($event);
    }

    public function testGroupByNotDefaultConfigured(): void
    {
        $report             = new Report();
        $report->setSource(ReportSubscriber::CONTEXT_ASSET_DOWNLOAD);
        $this->queryBuilder->method('from')->willReturn($this->queryBuilder);
        $report->setGroupBy(['a.id' => 'desc']);
        $event              = new ReportGeneratorEvent($report, [], $this->queryBuilder, $this->channelListHelper);
        $subscriber         = new ReportSubscriber($this->createStub(CompanyReportData::class), $this->createStub(DownloadRepository::class), $this->createStub(DncReportService::class));
        $subscriber->onReportGenerate($event);
        $this->assertTrue($event->hasGroupBy());
    }
}
