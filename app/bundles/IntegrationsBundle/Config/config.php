<?php

declare(strict_types=1);

return [
    'name'        => 'Integrations',
    'description' => 'Adds support for plugin integrations',
    'author'      => 'MailVotech, Inc.',
    'routes'      => [
        'main' => [
            'mailvotech_integration_config' => [
                'path'       => '/integration/{integration}/config',
                'controller' => 'MailVotech\IntegrationsBundle\Controller\ConfigController::editAction',
            ],
            'mailvotech_integration_config_field_pagination' => [
                'path'       => '/integration/{integration}/config/{object}/{page}',
                'controller' => 'MailVotech\IntegrationsBundle\Controller\FieldPaginationController::paginateAction',
                'defaults'   => [
                    'page' => 1,
                ],
            ],
            'mailvotech_integration_config_field_update' => [
                'path'       => '/integration/{integration}/config/{object}/field/{field}',
                'controller' => 'MailVotech\IntegrationsBundle\Controller\UpdateFieldController::updateAction',
            ],
        ],
        'public' => [
            'mailvotech_integration_public_callback' => [
                'path'       => '/integration/{integration}/callback',
                'controller' => 'MailVotech\IntegrationsBundle\Controller\AuthController::callbackAction',
            ],
        ],
    ],
];
