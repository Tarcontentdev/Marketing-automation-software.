<?php

declare(strict_types=1);

return [
    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.dynamicContent.dynamicContent' => [
                    'route'    => 'mailvotech_dynamicContent_index',
                    'access'   => ['dynamiccontent:dynamiccontents:viewown', 'dynamiccontent:dynamiccontents:viewother'],
                    'parent'   => 'mailvotech.core.components',
                    'priority' => 90,
                ],
            ],
        ],
    ],
    'categories' => [
        'dynamicContent' => [
            'class' => MailVotech\DynamicContentBundle\Entity\DynamicContent::class,
        ],
    ],
    'routes' => [
        'main' => [
            'mailvotech_dynamicContent_index' => [
                'path'       => '/dwc/{page}',
                'controller' => 'MailVotech\DynamicContentBundle\Controller\DynamicContentController::indexAction',
            ],
            'mailvotech_dynamicContent_action' => [
                'path'       => '/dwc/{objectAction}/{objectId}',
                'controller' => 'MailVotech\DynamicContentBundle\Controller\DynamicContentController::executeAction',
            ],
        ],
        'public' => [
            'mailvotech_api_dynamicContent_index' => [
                'path'       => '/dwc',
                'controller' => 'MailVotech\DynamicContentBundle\Controller\DynamicContentApiController::getAction',
            ],
            'mailvotech_api_dynamicContent_action' => [
                'path'       => '/dwc/{objectAlias}',
                'controller' => 'MailVotech\DynamicContentBundle\Controller\DynamicContentApiController::processAction',
            ],
        ],
        'api' => [
            'mailvotech_api_dynamicContent_standard' => [
                'standard_entity' => true,
                'name'            => 'dynamicContents',
                'path'            => '/dynamiccontents',
                'controller'      => MailVotech\DynamicContentBundle\Controller\Api\DynamicContentApiController::class,
            ],
        ],
    ],
];
