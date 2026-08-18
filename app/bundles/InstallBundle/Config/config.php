<?php

declare(strict_types=1);

return [
    'routes' => [
        'public' => [
            'mailvotech_installer_home' => [
                'path'       => '/installer',
                'controller' => 'MailVotech\InstallBundle\Controller\InstallController::stepAction',
            ],
            'mailvotech_installer_remove_slash' => [
                'path'       => '/installer/',
                'controller' => 'MailVotech\CoreBundle\Controller\CommonController::removeTrailingSlashAction',
            ],
            'mailvotech_installer_step' => [
                'path'       => '/installer/step/{index}',
                'controller' => 'MailVotech\InstallBundle\Controller\InstallController::stepAction',
            ],
            'mailvotech_installer_final' => [
                'path'       => '/installer/final',
                'controller' => 'MailVotech\InstallBundle\Controller\InstallController::finalAction',
            ],
            'mailvotech_installer_catchcall' => [
                'path'         => '/installer/{noerror}',
                'controller'   => 'MailVotech\InstallBundle\Controller\InstallController::stepAction',
                'requirements' => [
                    'noerror' => '^(?).+',
                ],
            ],
        ],
    ],
];
