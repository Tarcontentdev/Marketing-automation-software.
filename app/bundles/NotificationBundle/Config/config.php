<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_notification_index' => [
                'path'       => '/notifications/{page}',
                'controller' => 'MailVotech\NotificationBundle\Controller\NotificationController::indexAction',
            ],
            'mailvotech_notification_action' => [
                'path'       => '/notifications/{objectAction}/{objectId}',
                'controller' => 'MailVotech\NotificationBundle\Controller\NotificationController::executeAction',
            ],
            'mailvotech_notification_contacts' => [
                'path'       => '/notifications/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\NotificationBundle\Controller\NotificationController::contactsAction',
            ],
            'mailvotech_mobile_notification_index' => [
                'path'       => '/mobile_notifications/{page}',
                'controller' => 'MailVotech\NotificationBundle\Controller\MobileNotificationController::indexAction',
            ],
            'mailvotech_mobile_notification_action' => [
                'path'       => '/mobile_notifications/{objectAction}/{objectId}',
                'controller' => 'MailVotech\NotificationBundle\Controller\MobileNotificationController::executeAction',
            ],
            'mailvotech_mobile_notification_contacts' => [
                'path'       => '/mobile_notifications/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\NotificationBundle\Controller\MobileNotificationController::contactsAction',
            ],
        ],
        'public' => [
            'mailvotech_receive_notification' => [
                'path'       => '/notification/receive',
                'controller' => 'MailVotech\NotificationBundle\Controller\Api\NotificationApiController::receiveAction',
            ],
            'mailvotech_subscribe_notification' => [
                'path'       => '/notification/subscribe',
                'controller' => 'MailVotech\NotificationBundle\Controller\Api\NotificationApiController::subscribeAction',
            ],
            'mailvotech_notification_popup' => [
                'path'       => '/notification',
                'controller' => 'MailVotech\NotificationBundle\Controller\PopupController::indexAction',
            ],

            // JS / Manifest URL's
            'mailvotech_onesignal_worker' => [
                'path'       => '/OneSignalSDKWorker.js',
                'controller' => 'MailVotech\NotificationBundle\Controller\JsController::workerAction',
            ],
            'mailvotech_onesignal_updater' => [
                'path'       => '/OneSignalSDKUpdaterWorker.js',
                'controller' => 'MailVotech\NotificationBundle\Controller\JsController::updaterAction',
            ],
            'mailvotech_onesignal_manifest' => [
                'path'       => '/manifest.json',
                'controller' => 'MailVotech\NotificationBundle\Controller\JsController::manifestAction',
            ],
            'mailvotech_app_notification' => [
                'path'       => '/notification/appcallback',
                'controller' => 'MailVotech\NotificationBundle\Controller\AppCallbackController::indexAction',
            ],
        ],
        'api' => [
            'mailvotech_api_notificationsstandard' => [
                'standard_entity' => true,
                'name'            => 'notifications',
                'path'            => '/notifications',
                'controller'      => MailVotech\NotificationBundle\Controller\Api\NotificationApiController::class,
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.notification.notifications' => [
                    'route'  => 'mailvotech_notification_index',
                    'access' => ['notification:notifications:viewown', 'notification:notifications:viewother'],
                    'checks' => [
                        'integration' => [
                            'OneSignal' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'parent'   => 'mailvotech.core.channels',
                    'priority' => 80,
                ],
                'mailvotech.notification.mobile_notifications' => [
                    'route'  => 'mailvotech_mobile_notification_index',
                    'access' => ['notification:mobile_notifications:viewown', 'notification:mobile_notifications:viewother'],
                    'checks' => [
                        'integration' => [
                            'OneSignal' => [
                                'enabled'  => true,
                                'features' => [
                                    'mobile',
                                ],
                            ],
                        ],
                    ],
                    'parent'   => 'mailvotech.core.channels',
                    'priority' => 65,
                ],
            ],
        ],
    ],
    // 'categories' => [
    //    'notification' => null
    // ],
    'parameters' => [
        'notification_enabled'                        => false,
        'notification_landing_page_enabled'           => true,
        'notification_tracking_page_enabled'          => false,
        'notification_app_id'                         => null,
        'notification_rest_api_key'                   => null,
        'notification_safari_web_id'                  => null,
        'gcm_sender_id'                               => '482941778795',
        'notification_subdomain_name'                 => null,
        'welcomenotification_enabled'                 => true,
        'campaign_send_notification_to_author'        => true,
        'campaign_notification_email_addresses'       => null,
        'webhook_send_notification_to_author'         => true,
        'webhook_notification_email_addresses'        => null,
    ],
];
