<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_page_index' => [
                'path'       => '/pages/{page}',
                'controller' => 'MailVotech\PageBundle\Controller\PageController::indexAction',
            ],
            'mailvotech_page_action' => [
                'path'       => '/pages/{objectAction}/{objectId}',
                'controller' => 'MailVotech\PageBundle\Controller\PageController::executeAction',
            ],
            'mailvotech_page_results' => [
                'path'       => '/pages/results/{objectId}/{page}',
                'controller' => 'MailVotech\PageBundle\Controller\PageController::resultsAction',
            ],
            'mailvotech_page_export' => [
                'path'       => '/pages/results/{objectId}/export/{format}',
                'controller' => 'MailVotech\PageBundle\Controller\PageController::exportAction',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
        ],
        'public' => [
            'mailvotech_page_tracker' => [
                'path'       => '/mtracking.gif',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::trackingImageAction',
            ],
            'mailvotech_page_tracker_cors' => [
                'path'       => '/mtc/event',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::trackingAction',
            ],
            'mailvotech_page_tracker_getcontact' => [
                'path'       => '/mtc',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::getContactIdAction',
            ],
            'mailvotech_url_redirect' => [
                'path'       => '/r/{redirectId}',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::redirectAction',
            ],
            'mailvotech_page_redirect' => [
                'path'       => '/redirect/{redirectId}',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::redirectAction',
            ],
            'mailvotech_page_preview' => [
                'path'       => '/page/preview/{id}/{objectType}',
                'controller' => 'MailVotech\PageBundle\Controller\PublicController::previewAction',
                'defaults'   => ['objectType' => null],
            ],
        ],
        'api' => [
            'mailvotech_api_pagesstandard' => [
                'standard_entity' => true,
                'name'            => 'pages',
                'path'            => '/pages',
                'controller'      => MailVotech\PageBundle\Controller\Api\PageApiController::class,
            ],
        ],
        'catchall' => [
            'mailvotech_page_public' => [
                'path'         => '/{slug}',
                'controller'   => 'MailVotech\PageBundle\Controller\PublicController::indexAction',
                'requirements' => [
                    'slug' => '^(?!(_(profiler|wdt)|css|images|js|favicon.ico|apps/bundles/|plugins/)).+',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.page.pages' => [
                    'route'    => 'mailvotech_page_index',
                    'access'   => ['page:pages:viewown', 'page:pages:viewother'],
                    'parent'   => 'mailvotech.core.components',
                    'priority' => 100,
                ],
            ],
        ],
    ],

    'categories' => [
        'page' => [
            'class' => MailVotech\PageBundle\Entity\Page::class,
        ],
    ],

    'parameters' => [
        'cat_in_page_url'                       => false,
        'google_analytics'                      => null,
        'track_contact_by_ip'                   => false,
        'track_by_fingerprint'                  => false,
        'google_analytics_id'                   => null,
        'google_analytics_trackingpage_enabled' => false,
        'google_analytics_landingpage_enabled'  => false,
        'google_analytics_anonymize_ip'         => false,
        'facebook_pixel_id'                     => null,
        'facebook_pixel_trackingpage_enabled'   => false,
        'facebook_pixel_landingpage_enabled'    => false,
        'do_not_track_404_anonymous'            => false,
        'append_segment_id_tracking_url'        => false,
        'validate_page_hit_required_data'       => false,
    ],
];
