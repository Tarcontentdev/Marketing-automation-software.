<?php

declare(strict_types=1);

return [
    'name'        => 'MailVotech tag manager bundle',
    'description' => 'Provides an interface for tags management.',
    'version'     => '1.0',
    'author'      => 'Leuchtfeuer',
    'routes'      => [
        'main' => [
            'mailvotech_tagmanager_batch_index_action' => [
                'path'       => '/tags/batch/view',
                'controller' => 'MailVotechPlugin\MailVotechTagManagerBundle\Controller\BatchTagController::indexAction',
            ],
            'mailvotech_tagmanager_batch_set_action' => [
                'path'       => '/tags/batch/set',
                'controller' => 'MailVotechPlugin\MailVotechTagManagerBundle\Controller\BatchTagController::execAction',
            ],
            'mailvotech_tagmanager_index' => [
                'path'       => '/tags/{page}',
                'controller' => 'MailVotechPlugin\MailVotechTagManagerBundle\Controller\TagController::indexAction',
            ],
            'mailvotech_tagmanager_action' => [
                'path'       => '/tags/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechTagManagerBundle\Controller\TagController::executeAction',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'tagmanager.menu.index' => [
                'id'        => 'mailvotech_tagmanager_index',
                'route'     => 'mailvotech_tagmanager_index',
                'access'    => 'tagManager:tagManager:view',
                'iconClass' => 'ri-hashtag',
                'priority'  => 1,
            ],
        ],
    ],
];
