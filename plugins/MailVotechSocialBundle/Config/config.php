<?php

declare(strict_types=1);

return [
    'name'        => 'Social Media',
    'description' => 'Enables integrations with MailVotech supported social media services.',
    'version'     => '1.0',
    'author'      => 'MailVotech',

    'routes' => [
        'main' => [
            'mailvotech_social_index' => [
                'path'       => '/monitoring/{page}',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction',
            ],
            'mailvotech_social_action' => [
                'path'       => '/monitoring/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::executeAction',
            ],
            'mailvotech_social_contacts' => [
                'path'       => '/monitoring/view/{objectId}/contacts/{page}',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::contactsAction',
            ],
            'mailvotech_tweet_index' => [
                'path'       => '/tweets/{page}',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\TweetController::indexAction',
            ],
            'mailvotech_tweet_action' => [
                'path'       => '/tweets/{objectAction}/{objectId}',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\TweetController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_tweetsstandard' => [
                'standard_entity' => true,
                'name'            => 'tweets',
                'path'            => '/tweets',
                'controller'      => MailVotechPlugin\MailVotechSocialBundle\Controller\Api\TweetApiController::class,
            ],
        ],
        'public' => [
            'mailvotech_social_js_generate' => [
                'path'       => '/social/generate/{formName}.js',
                'controller' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\JsController::generateAction',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'mailvotech.social.monitoring' => [
                'route'    => 'mailvotech_social_index',
                'parent'   => 'mailvotech.core.channels',
                'access'   => 'mailvotechSocial:monitoring:view',
                'priority' => 0,
                'checks'   => [
                    'integration' => [
                        'Twitter' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            'mailvotech.social.tweets' => [
                'route'    => 'mailvotech_tweet_index',
                'access'   => ['mailvotechSocial:tweets:viewown', 'mailvotechSocial:tweets:viewother'],
                'parent'   => 'mailvotech.core.channels',
                'priority' => 80,
                'checks'   => [
                    'integration' => [
                        'Twitter' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],

    'categories' => [
        'plugin:mailvotechSocial' => [
            'label' => 'mailvotech.social.monitoring',
            'class' => MailVotechPlugin\MailVotechSocialBundle\Entity\Monitoring::class,
        ],
    ],

    'twitter' => [
        'tweet_request_count' => 100,
    ],

    'parameters' => [
        'twitter_handle_field' => 'twitter',
    ],
];
