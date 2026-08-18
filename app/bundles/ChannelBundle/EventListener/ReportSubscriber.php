<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\EventListener;

use MailVotech\LeadBundle\Model\CompanyReportData;
use MailVotech\ReportBundle\Event\ReportBuilderEvent;
use MailVotech\ReportBundle\Event\ReportDataEvent;
use MailVotech\ReportBundle\Event\ReportGeneratorEvent;
use MailVotech\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class ReportSubscriber implements EventSubscriberInterface
{
    public const CONTEXT_MESSAGE_CHANNEL = 'message.channel';

    public function __construct(
        private CompanyReportData $companyReportData,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvents::REPORT_ON_BUILD    => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_DISPLAY  => ['onReportDisplay', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event): void
    {
        if (!$event->checkContext([self::CONTEXT_MESSAGE_CHANNEL])) {
            return;
        }

        // message queue
        $prefix  = 'mq.';
        $columns = [
            $prefix.'channel' => [
                'label' => 'mailvotech.message.queue.report.channel',
                'type'  => 'html',
            ],
            $prefix.'channel_id' => [
                'label' => 'mailvotech.message.queue.report.channel_id',
                'type'  => 'int',
            ],
            $prefix.'priority' => [
                'label' => 'mailvotech.message.queue.report.priority',
                'type'  => 'string',
            ],
            $prefix.'max_attempts' => [
                'label' => 'mailvotech.message.queue.report.max_attempts',
                'type'  => 'int',
            ],
            $prefix.'attempts' => [
                'label' => 'mailvotech.message.queue.report.attempts',
                'type'  => 'int',
            ],
            $prefix.'success' => [
                'label' => 'mailvotech.message.queue.report.success',
                'type'  => 'boolean',
            ],
            $prefix.'status' => [
                'label' => 'mailvotech.message.queue.report.status',
                'type'  => 'string',
            ],
            $prefix.'last_attempt' => [
                'label' => 'mailvotech.message.queue.report.last_attempt',
                'type'  => 'datetime',
            ],
            $prefix.'date_sent' => [
                'label' => 'mailvotech.message.queue.report.date_sent',
                'type'  => 'datetime',
            ],
            $prefix.'scheduled_date' => [
                'label' => 'mailvotech.message.queue.report.scheduled_date',
                'type'  => 'datetime',
            ],
            $prefix.'date_published' => [
                'label' => 'mailvotech.message.queue.report.date_published',
                'type'  => 'datetime',
            ],
        ];

        $companyColumns = $this->companyReportData->getCompanyData();

        $columns = array_merge(
            $columns,
            $event->getLeadColumns(),
            $companyColumns
        );

        $event->addTable(
            self::CONTEXT_MESSAGE_CHANNEL,
            [
                'display_name' => 'mailvotech.message.queue',
                'columns'      => $columns,
            ]
        );
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event): void
    {
        if (!$event->checkContext([self::CONTEXT_MESSAGE_CHANNEL])) {
            return;
        }

        $queryBuilder = $event->getQueryBuilder();
        $queryBuilder->from(MAILVOTECH_TABLE_PREFIX.'message_queue', 'mq')
            ->leftJoin('mq', MAILVOTECH_TABLE_PREFIX.'leads', 'l', 'l.id = mq.lead_id');

        if ($this->companyReportData->eventHasCompanyColumns($event)) {
            $event->addCompanyLeftJoin($queryBuilder);
        }

        $event->setQueryBuilder($queryBuilder);
    }

    public function onReportDisplay(ReportDataEvent $event): void
    {
        $data = $event->getData();
        if ($event->checkContext([self::CONTEXT_MESSAGE_CHANNEL])) {
            if (isset($data[0]['channel']) && isset($data[0]['channel_id'])) {
                foreach ($data as &$row) {
                    $href = $this->router->generate('mailvotech_'.$row['channel'].'_action', ['objectAction' => 'view', 'objectId' => $row['channel_id']]);
                    if (isset($row['channel'])) {
                        $row['channel'] = '<a href="'.$href.'">'.$row['channel'].'</a>';
                    }
                    unset($row);
                }
            }
        }

        $event->setData($data);
        unset($data);
    }
}
