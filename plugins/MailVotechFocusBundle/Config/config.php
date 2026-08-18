<?php

declare(strict_types=1);

return [
    'name'        => 'MailVotech Focus',
    'description' => 'Drive visitor\'s focus on your website with MailVotech Focus',
    'version'     => '1.0',
    'author'      => 'MailVotech, Inc',

    'routes' => [
        'main' => [
            'mailvotech_focus_index' => [
                'path'       => '/focus/{page}',
                'controller' => 'MailVotechPlugin\MailVotechFocusBundle\Controller\FocusController::indexAction',
            ],
            'mailvotech_focus_action' => [
                'path'       => '/focus/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechFocusBundle\Controller\FocusController::executeAction',
            ],
        ],
        'public' => [
            'mailvotech_focus_generate' => [
                'path'       => '/focus/{id}.js',
                'controller' => 'MailVotechPlugin\MailVotechFocusBundle\Controller\PublicController::generateAction',
            ],
            'mailvotech_focus_pixel' => [
                'path'       => '/focus/{id}/viewpixel.gif',
                'controller' => 'MailVotechPlugin\MailVotechFocusBundle\Controller\PublicController::viewPixelAction',
            ],
        ],
        'api' => [
            'mailvotech_api_focusstandard' => [
                'standard_entity' => true,
                'name'            => 'focus',
                'path'            => '/focus',
                'controller'      => MailVotechPlugin\MailVotechFocusBundle\Controller\Api\FocusApiController::class,
            ],
            'mailvotech_api_focusjs' => [
                'path'       => '/focus/{id}/js',
                'controller' => 'MailVotechPlugin\MailVotechFocusBundle\Controller\Api\FocusApiController::generateJsAction',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.focus' => [
                'route'    => 'mailvotech_focus_index',
                'access'   => 'focus:items:view',
                'parent'   => 'mailvotech.core.channels',
                'priority' => 10,
            ],
        ],
    ],

    'categories' => [
        'plugin:focus' => [
            'label' => 'mailvotech.focus',
            'class' => MailVotechPlugin\MailVotechFocusBundle\Entity\Focus::class,
        ],
    ],

    'parameters' => [
        'website_snapshot_url' => 'https://mailvotech.net/api/snapshot',
        'website_snapshot_key' => '',
    ],
];
