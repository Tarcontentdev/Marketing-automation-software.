<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_report_index' => [
                'path'       => '/reports/{page}',
                'controller' => 'MailVotech\ReportBundle\Controller\ReportController::indexAction',
            ],
            'mailvotech_report_export' => [
                'path'       => '/reports/view/{objectId}/export/{format}',
                'controller' => 'MailVotech\ReportBundle\Controller\ReportController::exportAction',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'mailvotech_report_download' => [
                'path'       => '/reports/download/{reportId}/{format}',
                'controller' => 'MailVotech\ReportBundle\Controller\ReportController::downloadAction',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'mailvotech_report_view' => [
                'path'       => '/reports/view/{objectId}/{reportPage}',
                'controller' => 'MailVotech\ReportBundle\Controller\ReportController::viewAction',
                'defaults'   => [
                    'reportPage' => 1,
                ],
                'requirements' => [
                    'reportPage' => '\d+',
                ],
            ],
            'mailvotech_report_schedule_preview' => [
                'path'       => '/reports/schedule/preview/{isScheduled}/{scheduleUnit}/{scheduleDay}/{scheduleMonthFrequency}',
                'controller' => 'MailVotech\ReportBundle\Controller\ScheduleController::indexAction',
                'defaults'   => [
                    'isScheduled'            => 0,
                    'scheduleUnit'           => '',
                    'scheduleDay'            => '',
                    'scheduleMonthFrequency' => '',
                ],
            ],
            'mailvotech_report_schedule' => [
                'path'       => '/reports/schedule/{reportId}/now',
                'controller' => 'MailVotech\ReportBundle\Controller\ScheduleController::nowAction',
            ],
            'mailvotech_report_action' => [
                'path'       => '/reports/{objectAction}/{objectId}',
                'controller' => 'MailVotech\ReportBundle\Controller\ReportController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_reportsstandard' => [
                'standard_entity' => true,
                'name'            => 'reports',
                'path'            => '/reports',
                'controller'      => MailVotech\ReportBundle\Controller\Api\ReportApiController::class,
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.report.reports' => [
                'route'     => 'mailvotech_report_index',
                'iconClass' => 'ri-file-chart-2-fill',
                'access'    => [
                    'report:reports:viewown',
                    'report:reports:viewother',
                ],
                'priority' => 20,
            ],
        ],
    ],

    'parameters' => [
        'report_temp_dir'                     => '%mailvotech.application_dir%/media/files/temp',
        'report_export_batch_size'            => 1000,
        'report_export_max_filesize_in_bytes' => 5_000_000,
        'csv_always_enclose'                  => false,
    ],
];
