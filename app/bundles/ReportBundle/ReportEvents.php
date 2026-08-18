<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle;

/**
 * Events available for ReportBundle.
 */
final class ReportEvents
{
    /**
     * The mailvotech.report_pre_save event is dispatched right before a report is persisted.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    public const REPORT_PRE_SAVE = 'mailvotech.report_pre_save';

    /**
     * The mailvotech.report_post_save event is dispatched right after a report is persisted.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    public const REPORT_POST_SAVE = 'mailvotech.report_post_save';

    /**
     * The mailvotech.report_pre_delete event is dispatched prior to when a report is deleted.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    public const REPORT_PRE_DELETE = 'mailvotech.report_pre_delete';

    /**
     * The mailvotech.report_post_delete event is dispatched after a report is deleted.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportEvent instance.
     *
     * @var string
     */
    public const REPORT_POST_DELETE = 'mailvotech.report_post_delete';

    /**
     * The mailvotech.report_on_build event is dispatched before displaying the report builder form to allow
     * bundles to specify report sources and columns.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportBuilderEvent instance.
     *
     * @var string
     */
    public const REPORT_ON_BUILD = 'mailvotech.report_on_build';

    /**
     * The mailvotech.report_on_generate event is dispatched when generating a report to build the base query.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportGeneratorEvent instance.
     *
     * @var string
     */
    public const REPORT_ON_GENERATE = 'mailvotech.report_on_generate';

    /**
     * The mailvotech.report_query_pre_execute event is dispatched to allow a plugin to alter the query before execution.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportQueryEvent instance.
     *
     * @var string
     */
    public const REPORT_QUERY_PRE_EXECUTE = 'mailvotech.report_query_pre_execute';

    /**
     * The mailvotech.report_on_display event is dispatched when displaying a report.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportDataEvent instance.
     *
     * @var string
     */
    public const REPORT_ON_DISPLAY = 'mailvotech.report_on_display';

    /**
     * The mailvotech.report_on_graph_generate event is dispatched to generate a graph data.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportGraphEvent instance.
     *
     * @var string
     */
    public const REPORT_ON_GRAPH_GENERATE = 'mailvotech.report_on_graph_generate';

    /**
     * The mailvotech.report_schedule_send event is dispatched to send an exported report to a user.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ReportScheduleSendEvent instance.
     *
     * @var string
     */
    public const REPORT_SCHEDULE_SEND = 'mailvotech.report_schedule_send';

    /**
     * The mailvotech.report_on_column_collect event is dispatched during the report building to allow
     * bundles to add the columns of mapped objects.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\ColumnCollectEvent instance.
     *
     * @var string
     */
    public const REPORT_ON_COLUMN_COLLECT = 'mailvotech.report_on_column_collect';

    /**
     * The mailvotech.report_cleanup event is dispatched to cleanup report files after they had been sent via email.
     *
     * The event listener receives a MailVotech\ReportBundle\Event\PermanentReportFileCreated instance.
     *
     * @var string
     */
    public const REPORT_PERMANENT_FILE_CREATED = 'mailvotech.report_permanent_file_created';
}
