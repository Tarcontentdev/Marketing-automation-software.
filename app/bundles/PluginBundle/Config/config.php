<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_integration_auth_callback_secure' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MailVotech\PluginBundle\Controller\AuthController::authCallbackAction',
            ],
            'mailvotech_integration_auth_postauth_secure' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MailVotech\PluginBundle\Controller\AuthController::authStatusAction',
            ],
            'mailvotech_plugin_index' => [
                'path'       => '/plugins',
                'controller' => 'MailVotech\PluginBundle\Controller\PluginController::indexAction',
            ],
            'mailvotech_plugin_config' => [
                'path'       => '/plugins/config/{name}/{page}',
                'controller' => 'MailVotech\PluginBundle\Controller\PluginController::configAction',
            ],
            'mailvotech_plugin_info' => [
                'path'       => '/plugins/info/{name}',
                'controller' => 'MailVotech\PluginBundle\Controller\PluginController::infoAction',
            ],
            'mailvotech_plugin_reload' => [
                'path'       => '/plugins/reload',
                'controller' => 'MailVotech\PluginBundle\Controller\PluginController::reloadAction',
            ],
        ],
        'public' => [
            'mailvotech_integration_auth_user' => [
                'path'       => '/plugins/integrations/authuser/{integration}',
                'controller' => 'MailVotech\PluginBundle\Controller\AuthController::authUserAction',
            ],
            'mailvotech_integration_auth_callback' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MailVotech\PluginBundle\Controller\AuthController::authCallbackAction',
            ],
            'mailvotech_integration_auth_postauth' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MailVotech\PluginBundle\Controller\AuthController::authStatusAction',
            ],
        ],
    ],
    'menu' => [
        'admin' => [
            'priority' => 50,
            'items'    => [
                'mailvotech.plugin.plugins' => [
                    'id'        => 'mailvotech_plugin_root',
                    'access'    => 'plugin:plugins:manage',
                    'route'     => 'mailvotech_plugin_index',
                    'parent'    => 'mailvotech.core.integrations',
                    'iconClass' => 'ri-plug-line',
                ],
            ],
        ],
    ],
];
