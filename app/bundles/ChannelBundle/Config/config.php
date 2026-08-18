<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_message_index' => [
                'path'       => '/messages/{page}',
                'controller' => 'MailVotech\ChannelBundle\Controller\MessageController::indexAction',
            ],
            'mailvotech_message_contacts' => [
                'path'       => '/messages/contacts/{objectId}/{channel}/{page}',
                'controller' => 'MailVotech\ChannelBundle\Controller\MessageController::contactsAction',
            ],
            'mailvotech_message_action' => [
                'path'       => '/messages/{objectAction}/{objectId}',
                'controller' => 'MailVotech\ChannelBundle\Controller\MessageController::executeAction',
            ],
            'mailvotech_channel_batch_contact_set' => [
                'path'       => '/channels/batch/contact/set',
                'controller' => 'MailVotech\ChannelBundle\Controller\BatchContactController::setAction',
            ],
            'mailvotech_channel_batch_contact_view' => [
                'path'       => '/channels/batch/contact/view',
                'controller' => 'MailVotech\ChannelBundle\Controller\BatchContactController::indexAction',
            ],
        ],
        'api' => [
            'mailvotech_api_messagetandard' => [
                'standard_entity' => true,
                'name'            => 'messages',
                'path'            => '/messages',
                'controller'      => MailVotech\ChannelBundle\Controller\Api\MessageApiController::class,
            ],
        ],
        'public' => [
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.channel.messages' => [
                'route'    => 'mailvotech_message_index',
                'access'   => ['channel:messages:viewown', 'channel:messages:viewother'],
                'parent'   => 'mailvotech.core.channels',
                'priority' => 110,
            ],
        ],
        'admin' => [
        ],
        'profile' => [
        ],
        'extra' => [
        ],
    ],

    'categories' => [
        'messages' => [
            'class' => MailVotech\ChannelBundle\Entity\Message::class,
        ],
    ],

    'parameters' => [
    ],
];
