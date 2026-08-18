<?php

declare(strict_types=1);

return [
    'name'        => 'Clearbit',
    'description' => 'Enables integration with Clearbit for contact and company lookup',
    'version'     => '1.0',
    'author'      => 'Werner Garcia',

    'routes' => [
        'public' => [
            'mailvotech_plugin_clearbit_index' => [
                'path'       => '/clearbit/callback',
                'controller' => 'MailVotechPlugin\MailVotechClearbitBundle\Controller\PublicController::callbackAction',
            ],
        ],
        'main' => [
            'mailvotech_plugin_clearbit_action' => [
                'path'       => '/clearbit/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechClearbitBundle\Controller\ClearbitController::executeAction',
            ],
        ],
    ],
];
