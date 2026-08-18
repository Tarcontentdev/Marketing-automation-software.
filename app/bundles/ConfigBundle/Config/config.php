<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_config_action' => [
                'path'       => '/config/{objectAction}/{objectId}',
                'controller' => 'MailVotech\ConfigBundle\Controller\ConfigController::executeAction',
            ],
            'mailvotech_sysinfo_index' => [
                'path'       => '/sysinfo',
                'controller' => 'MailVotech\ConfigBundle\Controller\SysinfoController::indexAction',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'mailvotech.config.menu.index' => [
                'route'           => 'mailvotech_config_action',
                'routeParameters' => ['objectAction' => 'edit'],
                'iconClass'       => 'ri-settings-5-line',
                'id'              => 'mailvotech_config_index',
                'parent'          => 'mailvotech.core.general',
                'access'          => 'admin',
                'priority'        => 16,
            ],
            'mailvotech.sysinfo.menu.index' => [
                'route'     => 'mailvotech_sysinfo_index',
                'iconClass' => 'ri-information-2-line',
                'id'        => 'mailvotech_sysinfo_index',
                'parent'    => 'mailvotech.core.general',
                'access'    => 'admin',
                'priority'  => 04,
                'checks'    => [
                    'parameters' => [
                        'sysinfo_disabled' => false,
                    ],
                ],
            ],
        ],
    ],

    'parameters' => [
        'config_allowed_parameters' => [
            'kernel.project_dir',
            'kernel.logs_dir',
        ],
    ],
];
