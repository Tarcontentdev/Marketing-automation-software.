<?php

declare(strict_types=1);

return [
    'name'        => 'FullContact',
    'description' => 'Enables integration with FullContact for contact and company lookup',
    'version'     => '1.0',
    'author'      => 'MailVotech',

    'routes' => [
        'public' => [
            'mailvotech_plugin_fullcontact_index' => [
                'path'       => '/fullcontact/callback',
                'controller' => 'MailVotechPlugin\MailVotechFullContactBundle\Controller\PublicController::callbackAction',
            ],
        ],
        'main' => [
            'mailvotech_plugin_fullcontact_action' => [
                'path'       => '/fullcontact/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechFullContactBundle\Controller\FullContactController::executeAction',
            ],
        ],
    ],
];
