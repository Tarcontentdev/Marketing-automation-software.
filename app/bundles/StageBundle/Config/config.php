<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_stage_index' => [
                'path'       => '/stages/{page}',
                'controller' => 'MailVotech\StageBundle\Controller\StageController::indexAction',
            ],
            'mailvotech_stage_action' => [
                'path'       => '/stages/{objectAction}/{objectId}',
                'controller' => 'MailVotech\StageBundle\Controller\StageController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_stagesstandard' => [
                'standard_entity' => true,
                'name'            => 'stages',
                'path'            => '/stages',
                'controller'      => MailVotech\StageBundle\Controller\Api\StageApiController::class,
            ],
            'mailvotech_api_stageddcontact' => [
                'path'       => '/stages/{id}/contact/{contactId}/add',
                'controller' => 'MailVotech\StageBundle\Controller\Api\StageApiController::addContactAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_stageremovecontact' => [
                'path'       => '/stages/{id}/contact/{contactId}/remove',
                'controller' => 'MailVotech\StageBundle\Controller\Api\StageApiController::removeContactAction',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.stages.menu.index' => [
                'route'     => 'mailvotech_stage_index',
                'iconClass' => 'ri-barricade-fill flip-vertically',
                'access'    => ['stage:stages:view'],
                'priority'  => 25,
            ],
        ],
    ],

    'categories' => [
        'stage' => [
            'class' => MailVotech\StageBundle\Entity\Stage::class,
        ],
    ],
];
