<?php

declare(strict_types=1);

return [
    'routes' => [
        'public' => [
            // OAuth2
            'fos_oauth_server_token' => [
                'path'       => '/oauth/v2/token',
                'controller' => 'fos_oauth_server.controller.token::tokenAction',
                'method'     => 'GET|POST',
            ],
            'fos_oauth_server_authorize' => [
                'path'       => '/oauth/v2/authorize',
                'controller' => 'MailVotech\ApiBundle\Controller\oAuth2\AuthorizeController::authorizeAction',
                'method'     => 'GET|POST',
            ],
            'mailvotech_oauth2_server_auth_login' => [
                'path'       => '/oauth/v2/authorize_login',
                'controller' => 'MailVotech\ApiBundle\Controller\oAuth2\SecurityController::loginAction',
                'method'     => 'GET|POST',
            ],
            'mailvotech_oauth2_server_auth_login_check' => [
                'path'       => '/oauth/v2/authorize_login_check',
                'controller' => 'MailVotech\ApiBundle\Controller\oAuth2\SecurityController::loginCheckAction',
                'method'     => 'GET|POST',
            ],
        ],
        'main' => [
            // Clients
            'mailvotech_client_index' => [
                'path'       => '/credentials/{page}',
                'controller' => 'MailVotech\ApiBundle\Controller\ClientController::indexAction',
            ],
            'mailvotech_client_action' => [
                'path'       => '/credentials/{objectAction}/{objectId}',
                'controller' => 'MailVotech\ApiBundle\Controller\ClientController::executeAction',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'items' => [
                'mailvotech.api.client.menu.index' => [
                    'route'     => 'mailvotech_client_index',
                    'access'    => 'api:clients:view',
                    'parent'    => 'mailvotech.core.integrations',
                    'iconClass' => 'ri-terminal-box-line',
                    'priority'  => 17,
                    'checks'    => [
                        'parameters' => [
                            'api_enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'parameters' => [
        'api_enabled'                       => false,
        'api_enable_basic_auth'             => false,
        'api_oauth2_access_token_lifetime'  => 60,
        'api_oauth2_refresh_token_lifetime' => 14,
        'api_batch_max_limit'               => 200,
    ],
];
