<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_dashboard_index' => [
                'path'       => '/dashboard',
                'controller' => 'MailVotech\DashboardBundle\Controller\DashboardController::indexAction',
            ],
            'mailvotech_dashboard_widget' => [
                'path'       => '/dashboard/widget/{widgetId}',
                'controller' => 'MailVotech\DashboardBundle\Controller\DashboardController::widgetAction',
            ],
            'mailvotech_dashboard_action' => [
                'path'       => '/dashboard/{objectAction}/{objectId}',
                'controller' => 'MailVotech\DashboardBundle\Controller\DashboardController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_widget_types' => [
                'path'       => '/data',
                'controller' => 'MailVotech\DashboardBundle\Controller\Api\WidgetApiController::getTypesAction',
            ],
            'mailvotech_widget_data' => [
                'path'       => '/data/{type}',
                'controller' => 'MailVotech\DashboardBundle\Controller\Api\WidgetApiController::getDataAction',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'priority' => 100,
            'items'    => [
                'mailvotech.dashboard.menu.index' => [
                    'route'     => 'mailvotech_dashboard_index',
                    'iconClass' => 'ri-funds-fill',
                ],
            ],
        ],
    ],
    'parameters' => [
        'dashboard_import_dir'      => '%mailvotech.application_dir%/app/assets/dashboards',
        'dashboard_import_user_dir' => '%mailvotech.application_dir%/media/dashboards',
    ],
];
