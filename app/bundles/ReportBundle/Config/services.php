<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Generator',
        'Builder/MailVotechReportBuilder.php',
        'Form/DataTransformer/ReportFilterDataTransformer.php',
        'Scheduler/Entity',
        'Scheduler/Option',
    ];

    $services->load('MailVotech\\ReportBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\ReportBundle\\Entity\\', '../Entity/*Repository.php');
    $services->set('mailvotech.report.validator.schedule_is_valid_validator', MailVotech\ReportBundle\Scheduler\Validator\ScheduleIsValidValidator::class)->tag('validator.constraint_validator');
    $services->set('mailvotech.report.model.scheduler_builder', MailVotech\ReportBundle\Scheduler\Builder\SchedulerBuilder::class);
    $services->set('mailvotech.report.model.scheduler_template_factory', MailVotech\ReportBundle\Scheduler\Factory\SchedulerTemplateFactory::class);
    $services->set('mailvotech.report.model.scheduler_date_builder', MailVotech\ReportBundle\Scheduler\Date\DateBuilder::class);
    $services->set('mailvotech.report.model.scheduler_planner', MailVotech\ReportBundle\Scheduler\Model\SchedulerPlanner::class);
    $services->set('mailvotech.report.model.send_schedule', MailVotech\ReportBundle\Scheduler\Model\SendSchedule::class);
    $services->set('mailvotech.report.model.file_handler', MailVotech\ReportBundle\Scheduler\Model\FileHandler::class);
    $services->set('mailvotech.report.model.message_schedule', MailVotech\ReportBundle\Scheduler\Model\MessageSchedule::class);
    $services->set('mailvotech.report.model.report_data_adapter', MailVotech\ReportBundle\Adapter\ReportDataAdapter::class);

    $services->alias('mailvotech.report.repository.scheduler', MailVotech\ReportBundle\Entity\SchedulerRepository::class);

    $services->alias('mailvotech.report.model.report', MailVotech\ReportBundle\Model\ReportModel::class);
    $services->alias('mailvotech.report.model.csv_exporter', MailVotech\ReportBundle\Model\CsvExporter::class);
    $services->alias('mailvotech.report.model.excel_exporter', MailVotech\ReportBundle\Model\ExcelExporter::class);
    $services->alias('mailvotech.report.model.report_exporter', MailVotech\ReportBundle\Model\ReportExporter::class);
    $services->alias('mailvotech.report.model.schedule_model', MailVotech\ReportBundle\Model\ScheduleModel::class);
    $services->alias('mailvotech.report.model.report_export_options', MailVotech\ReportBundle\Model\ReportExportOptions::class);
    $services->alias('mailvotech.report.model.report_file_writer', MailVotech\ReportBundle\Model\ReportFileWriter::class);
    $services->alias('mailvotech.report.model.export_handler', MailVotech\ReportBundle\Model\ExportHandler::class);

    $services->set(MailVotech\ReportBundle\Helper\ReportHelper::class)
        ->tag('twig.helper', ['alias' => 'report']);
};
