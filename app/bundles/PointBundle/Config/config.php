<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_pointtriggerevent_action' => [
                'path'       => '/points/triggers/events/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PointBundle\Controller\TriggerEventController::executeAction',
            ],
            'mailvotech_pointtrigger_index' => [
                'path'       => '/points/triggers/{page}',
                'controller' => 'MailVotech\PointBundle\Controller\TriggerController::indexAction',
            ],
            'mailvotech_pointtrigger_action' => [
                'path'       => '/points/triggers/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PointBundle\Controller\TriggerController::executeAction',
            ],
            'mailvotech_point.group_index' => [
                'path'       => '/points/groups/{page}',
                'controller' => 'MailVotech\PointBundle\Controller\GroupController::indexAction',
            ],
            'mailvotech_point.group_action' => [
                'path'       => '/points/groups/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PointBundle\Controller\GroupController::executeAction',
            ],
            'mailvotech_point.insight_index' => [
                'path'       => '/points/insights/{page}',
                'controller' => 'MailVotech\PointBundle\Controller\InsightController::indexAction',
            ],
            'mailvotech_point.insight_action' => [
                'path'       => '/points/insights/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PointBundle\Controller\InsightController::executeAction',
            ],
            'mailvotech_point_index' => [
                'path'       => '/points/{page}',
                'controller' => 'MailVotech\PointBundle\Controller\PointController::indexAction',
            ],
            'mailvotech_point_action' => [
                'path'       => '/points/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PointBundle\Controller\PointController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_pointactionsstandard' => [
                'standard_entity' => true,
                'name'            => 'points',
                'path'            => '/points',
                'controller'      => MailVotech\PointBundle\Controller\Api\PointApiController::class,
            ],
            'mailvotech_api_getpointactiontypes' => [
                'path'       => '/points/actions/types',
                'controller' => 'MailVotech\PointBundle\Controller\Api\PointApiController::getPointActionTypesAction',
            ],
            'mailvotech_api_pointtriggersstandard' => [
                'standard_entity' => true,
                'name'            => 'triggers',
                'path'            => '/points/triggers',
                'controller'      => MailVotech\PointBundle\Controller\Api\TriggerApiController::class,
            ],
            'mailvotech_api_getpointtriggereventtypes' => [
                'path'       => '/points/triggers/events/types',
                'controller' => 'MailVotech\PointBundle\Controller\Api\TriggerApiController::getPointTriggerEventTypesAction',
            ],
            'mailvotech_api_pointtriggerdeleteevents' => [
                'path'       => '/points/triggers/{triggerId}/events/delete',
                'controller' => 'MailVotech\PointBundle\Controller\Api\TriggerApiController::deletePointTriggerEventsAction',
                'method'     => 'DELETE',
            ],
            'mailvotech_api_adjustcontactpoints' => [
                'path'       => '/contacts/{leadId}/points/{operator}/{delta}',
                'controller' => 'MailVotech\PointBundle\Controller\Api\PointApiController::adjustPointsAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_pointgroupsstandard' => [
                'standard_entity' => true,
                'name'            => 'pointGroups',
                'path'            => '/points/groups',
                'controller'      => MailVotech\PointBundle\Controller\Api\PointGroupsApiController::class,
            ],
            'mailvotech_api_getcontactpointgroups' => [
                'path'       => '/contacts/{contactId}/points/groups',
                'controller' => 'MailVotech\PointBundle\Controller\Api\PointGroupsApiController::getContactPointGroupsAction',
            ],
            'mailvotech_api_getcontactpointgroup' => [
                'path'       => '/contacts/{contactId}/points/groups/{groupId}',
                'controller' => 'MailVotech\PointBundle\Controller\Api\PointGroupsApiController::getContactPointGroupAction',
            ],
            'mailvotech_api_adjustcontactgrouppoints' => [
                'path'       => '/contacts/{contactId}/points/groups/{groupId}/{operator}/{value}',
                'controller' => 'MailVotech\PointBundle\Controller\Api\PointGroupsApiController::adjustGroupPointsAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_pointinsightsstandard' => [
                'standard_entity' => true,
                'name'            => 'insights',
                'path'            => '/points/insights',
                'controller'      => MailVotech\PointBundle\Controller\Api\PointInsightApiController::class,
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.points.menu.root' => [
                'id'        => 'mailvotech_points_root',
                'iconClass' => 'ri-coins-fill',
                'access'    => ['point:points:view', 'point:triggers:view', 'point:groups:view'],
                'priority'  => 30,
                'children'  => [
                    'mailvotech.point.menu.index' => [
                        'route'  => 'mailvotech_point_index',
                        'access' => 'point:points:view',
                    ],
                    'mailvotech.point.trigger.menu.index' => [
                        'route'  => 'mailvotech_pointtrigger_index',
                        'access' => 'point:triggers:view',
                    ],
                    'mailvotech.point.group.menu.index' => [
                        'route'  => 'mailvotech_point.group_index',
                        'access' => 'point:groups:view',
                    ],
                    'mailvotech.point.insights.menu' => [
                        'route'  => 'mailvotech_point.insight_index',
                        'access' => 'point:insights:view',
                    ],
                ],
            ],
        ],
    ],

    'categories' => [
        'point' => [
            'class' => MailVotech\PointBundle\Entity\Point::class,
        ],
    ],
];
