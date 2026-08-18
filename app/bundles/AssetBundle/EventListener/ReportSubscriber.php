<?php

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\Entity\DownloadRepository;
use MailVotech\CoreBundle\Helper\Chart\LineChart;
use MailVotech\LeadBundle\Model\CompanyReportData;
use MailVotech\LeadBundle\Report\DncReportService;
use MailVotech\ReportBundle\Event\ReportBuilderEvent;
use MailVotech\ReportBundle\Event\ReportDataEvent;
use MailVotech\ReportBundle\Event\ReportGeneratorEvent;
use MailVotech\ReportBundle\Event\ReportGraphEvent;
use MailVotech\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ReportSubscriber implements EventSubscriberInterface
{
    public const CONTEXT_ASSET          = 'assets';

    public const CONTEXT_ASSET_DOWNLOAD = 'asset.downloads';

    public function __construct(
        private CompanyReportData $companyReportData,
        private DownloadRepository $downloadRepository,
        private DncReportService $dncReportService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvents::REPORT_ON_BUILD          => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE       => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_GRAPH_GENERATE => ['onReportGraphGenerate', 0],
            ReportEvents::REPORT_ON_DISPLAY        => ['onReportDisplay', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event): void
    {
        if (!$event->checkContext([self::CONTEXT_ASSET, self::CONTEXT_ASSET_DOWNLOAD])) {
            return;
        }

        // Assets
        $prefix  = 'a.';
        $columns = [
            $prefix.'download_count' => [
                'alias' => 'download_count',
                'label' => 'mailvotech.asset.report.download_count',
                'type'  => 'int',
            ],
            $prefix.'unique_download_count' => [
                'alias' => 'unique_download_count',
                'label' => 'mailvotech.asset.report.unique_download_count',
                'type'  => 'int',
            ],
            $prefix.'alias' => [
                'label' => 'mailvotech.core.alias',
                'type'  => 'string',
            ],
            $prefix.'lang' => [
                'label' => 'mailvotech.core.language',
                'type'  => 'string',
            ],
            $prefix.'title' => [
                'label' => 'mailvotech.core.title',
                'type'  => 'string',
            ],
        ];

        $columns = array_merge(
            $columns,
            $event->getStandardColumns($prefix, ['name'], 'mailvotech_asset_action'),
            $event->getCategoryColumns()
        );

        $event->addTable(
            self::CONTEXT_ASSET,
            [
                'display_name' => 'mailvotech.asset.assets',
                'columns'      => $columns,
            ]
        );

        if ($event->checkContext([self::CONTEXT_ASSET_DOWNLOAD])) {
            // asset downloads calculate this columns
            $columns[$prefix.'download_count']['formula']        = 'COUNT(ad.id)';
            $columns[$prefix.'unique_download_count']['formula'] = 'COUNT(DISTINCT ad.lead_id)';

            // Downloads
            $downloadPrefix  = 'ad.';
            $downloadColumns = [
                $downloadPrefix.'date_download' => [
                    'label'          => 'mailvotech.asset.report.download.date_download',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$downloadPrefix.'date_download)',
                ],
                $downloadPrefix.'code' => [
                    'label' => 'mailvotech.asset.report.download.code',
                    'type'  => 'string',
                ],
                $downloadPrefix.'referer' => [
                    'label' => 'mailvotech.core.referer',
                    'type'  => 'string',
                ],
                $downloadPrefix.'source' => [
                    'label' => 'mailvotech.report.field.source',
                    'type'  => 'string',
                ],
                $downloadPrefix.'source_id' => [
                    'label' => 'mailvotech.report.field.source_id',
                    'type'  => 'int',
                ],
                $downloadPrefix.'utm_campaign' => [
                    'label' => 'mailvotech.report.field.utm_campaign',
                    'type'  => 'string',
                ],
                $downloadPrefix.'utm_content' => [
                    'label' => 'mailvotech.report.field.utm_content',
                    'type'  => 'string',
                ],
                $downloadPrefix.'utm_medium' => [
                    'label' => 'mailvotech.report.field.utm_medium',
                    'type'  => 'string',
                ],
                $downloadPrefix.'utm_source' => [
                    'label' => 'mailvotech.report.field.utm_source',
                    'type'  => 'string',
                ],
                $downloadPrefix.'utm_term' => [
                    'label' => 'mailvotech.report.field.utm_term',
                    'type'  => 'string',
                ],
            ];

            $companyColumns          = $this->companyReportData->getCompanyData();
            $commonColumnsAndFilters = array_merge(
                $columns,
                $downloadColumns,
                $event->getCampaignByChannelColumns(),
                $event->getLeadColumns(),
                $event->getIpColumn(),
                $companyColumns
            );

            $assetDownloadColumns = array_merge($commonColumnsAndFilters, $this->dncReportService->getDncColumns());
            $assetDownloadFilters = array_merge($commonColumnsAndFilters, $this->dncReportService->getDncFilters());

            $event->addTable(
                self::CONTEXT_ASSET_DOWNLOAD,
                [
                    'display_name' => 'mailvotech.asset.report.downloads.table',
                    'columns'      => $assetDownloadColumns,
                    'filters'      => $assetDownloadFilters,
                ],
                self::CONTEXT_ASSET
            );

            // Add Graphs
            $context = self::CONTEXT_ASSET_DOWNLOAD;
            $event->addGraph($context, 'line', 'mailvotech.asset.graph.line.downloads');
            $event->addGraph($context, 'table', 'mailvotech.asset.table.most.downloaded');
            $event->addGraph($context, 'table', 'mailvotech.asset.table.top.referrers');
            $event->addGraph($context, 'pie', 'mailvotech.asset.graph.pie.statuses', ['translate' => false]);
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event): void
    {
        if (!$event->checkContext([self::CONTEXT_ASSET, self::CONTEXT_ASSET_DOWNLOAD])) {
            return;
        }

        $queryBuilder = $event->getQueryBuilder();

        if ($event->checkContext(self::CONTEXT_ASSET)) {
            $queryBuilder->from(MAILVOTECH_TABLE_PREFIX.'assets', 'a');
            $event->addCategoryLeftJoin($queryBuilder, 'a');
        } elseif ($event->checkContext(self::CONTEXT_ASSET_DOWNLOAD)) {
            $event->applyDateFilters($queryBuilder, 'date_download', 'ad');

            $queryBuilder->from(MAILVOTECH_TABLE_PREFIX.'asset_downloads', 'ad')
                ->leftJoin('ad', MAILVOTECH_TABLE_PREFIX.'assets', 'a', 'a.id = ad.asset_id');
            $event->addCategoryLeftJoin($queryBuilder, 'a');
            $event->addLeadLeftJoin($queryBuilder, 'ad');
            $event->addIpAddressLeftJoin($queryBuilder, 'ad');
            $event->addCampaignByChannelJoin($queryBuilder, 'a', 'asset');

            if ($this->companyReportData->eventHasCompanyColumns($event)) {
                $event->addCompanyLeftJoin($queryBuilder);
            }

            if (!$event->hasGroupBy()) {
                $queryBuilder->groupBy('ad.id');
            }
        }

        $event->setQueryBuilder($queryBuilder);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGraphGenerate(ReportGraphEvent $event): void
    {
        // Context check, we only want to fire for Lead reports
        if (!$event->checkContext(self::CONTEXT_ASSET_DOWNLOAD)) {
            return;
        }

        $graphs = $event->getRequestedGraphs();
        $qb     = $event->getQueryBuilder();

        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;
            $chartQuery   = clone $options['chartQuery'];
            $chartQuery->applyDateFilters($queryBuilder, 'date_download', 'ad');

            switch ($g) {
                case 'mailvotech.asset.graph.line.downloads':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $chartQuery->modifyTimeDataQuery($queryBuilder, 'date_download', 'ad');
                    $downloads = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $downloads);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;
                case 'mailvotech.asset.table.most.downloaded':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->downloadRepository->getMostDownloaded($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'ri-download-line';
                    $graphData['link']      = 'mailvotech_asset_action';
                    $event->setGraph($g, $graphData);
                    break;
                case 'mailvotech.asset.table.top.referrers':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $this->downloadRepository->getTopReferrers($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'ri-download-line';
                    $graphData['link']      = 'mailvotech_asset_action';
                    $event->setGraph($g, $graphData);
                    break;
                case 'mailvotech.asset.graph.pie.statuses':
                    $items                  = $this->downloadRepository->getHttpStatuses($queryBuilder);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'ri-earth-line';
                    $event->setGraph($g, $graphData);
                    break;
            }

            unset($queryBuilder);
        }
    }

    public function onReportDisplay(ReportDataEvent $event): void
    {
        $data = $event->getData();

        if ($event->checkContext([self::CONTEXT_ASSET_DOWNLOAD])) {
            $data = $this->dncReportService->processDncStatusDisplay($data);
        }

        $event->setData($data);
        unset($data);
    }
}
