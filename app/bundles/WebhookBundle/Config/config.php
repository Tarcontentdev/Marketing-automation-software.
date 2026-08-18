<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_webhook_index' => [
                'path'       => '/webhooks/{page}',
                'controller' => 'MailVotech\WebhookBundle\Controller\WebhookController::indexAction',
            ],
            'mailvotech_webhook_action' => [
                'path'       => '/webhooks/{objectAction}/{objectId}',
                'controller' => 'MailVotech\WebhookBundle\Controller\WebhookController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_webhookstandard' => [
                'standard_entity' => true,
                'name'            => 'hooks',
                'path'            => '/hooks',
                'controller'      => MailVotech\WebhookBundle\Controller\Api\WebhookApiController::class,
            ],
            'mailvotech_api_webhookevents' => [
                'path'       => '/hooks/triggers',
                'controller' => 'MailVotech\WebhookBundle\Controller\Api\WebhookApiController::getTriggersAction',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'items' => [
                'mailvotech.webhook.webhooks' => [
                    'id'        => 'mailvotech_webhook_root',
                    'access'    => ['webhook:webhooks:viewown', 'webhook:webhooks:viewother'],
                    'route'     => 'mailvotech_webhook_index',
                    'parent'    => 'mailvotech.core.integrations',
                    'iconClass' => 'ri-webhook-fill',
                ],
            ],
        ],
    ],

    'parameters' => [
        'webhook_limit'                            => 10, // How many entities can be sent in one webhook
        'webhook_time_limit'                       => 600, // How long the webhook processing can run in seconds
        'webhook_log_max'                          => 1000, // How many recent logs to keep
        'webhook_health_check_time'                => 300, // Retry webhook after this time once it marked it as unhealthy in seconds.
        'webhook_retry_delay'                      => 3600, // Retry webhook_queue entry after given time after it is failed in seconds.
        'clean_webhook_logs_in_background'         => false,
        'webhook_disable_limit'                    => 100, // How many times the webhook response can fail until the webhook will be unpublished
        'webhook_timeout'                          => 15, // How long the CURL request can wait for response before MailVotech hangs up. In seconds
        'queue_mode'                               => MailVotech\WebhookBundle\Model\WebhookModel::IMMEDIATE_PROCESS, // Trigger the webhook immediately or queue it for faster response times
        'events_orderby_dir'                       => Doctrine\Common\Collections\Order::Ascending->value, // Order the queued events chronologically or the other way around
        'webhook_email_details'                    => true, // If enabled, email related webhooks send detailed data
        'disable_auto_unpublish'                   => false, // If enabled, webhooks will not be automatically unpublished on errors
        'first_webhook_failure_notification_time'  => 3600, // 1 hour
        'webhook_failure_notification_interval'    => 86400, // 1 day
        'webhook_allowed_private_addresses'        => [],
    ],
];
