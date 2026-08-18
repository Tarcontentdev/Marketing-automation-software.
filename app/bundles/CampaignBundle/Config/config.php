<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_campaignevent_action'  => [
                'path'       => '/campaigns/events/{objectAction}/{objectId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\EventController::executeAction',
            ],
            'mailvotech_campaignsource_action' => [
                'path'       => '/campaigns/sources/{objectAction}/{objectId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\SourceController::executeAction',
            ],
            'mailvotech_campaign_index'        => [
                'path'       => '/campaigns/{page}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignController::indexAction',
            ],
            'mailvotech_campaign_action'       => [
                'path'       => '/campaigns/{objectAction}/{objectId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignController::executeAction',
            ],
            'mailvotech_campaign_contacts'     => [
                'path'       => '/campaigns/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignController::contactsAction',
            ],
            'mailvotech_campaign_event_stats'     => [
                'path'       => '/campaigns/event/stats/{objectId}/{dateFromValue}/{dateToValue}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignController::eventStatsAction',
            ],
            'mailvotech_campaign_graph'     => [
                'path'       => '/campaigns/graph/{objectId}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignController::graphAction',
            ],
            'mailvotech_campaign_preview'      => [
                'path'       => '/campaign/preview/{objectId}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::previewAction',
            ],
            'mailvotech_campaign_map_stats'    => [
                'path'       => '/campaign-map-stats/{objectId}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignMapStatsController::viewAction',
            ],
            'mailvotech_campaign_metrics_email_weekdays' => [
                'path'       => '/campaign/metrics/email-weekdays/{objectId}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignMetricsController::emailWeekdaysAction',
            ],
            'mailvotech_campaign_metrics_email_hours' => [
                'path'       => '/campaign/metrics/email-hours/{objectId}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignMetricsController::emailHoursAction',
            ],
            'mailvotech_campaign_import_index' => [
                'path'       => '/campaign/import',
                'controller' => 'MailVotech\CampaignBundle\Controller\ImportController::indexAction',
            ],
            'mailvotech_campaign_import_action' => [
                'path'       => '/campaign/import/{objectAction}',
                'controller' => 'MailVotech\CampaignBundle\Controller\ImportController::executeAction',
            ],
            'mailvotech_campaign_metrics_event_details' => [
                'path'       => '/campaign/metrics/event-details/{objectId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\CampaignMetricsController::eventDetailsAction',
            ],
        ],
        'api'  => [
            'mailvotech_api_campaignsstandard'            => [
                'standard_entity' => true,
                'name'            => 'campaigns',
                'path'            => '/campaigns',
                'controller'      => MailVotech\CampaignBundle\Controller\Api\CampaignApiController::class,
            ],
            'mailvotech_api_campaigneventsstandard'       => [
                'standard_entity'     => true,
                'supported_endpoints' => [
                    'getone',
                    'getall',
                ],
                'name'                => 'events',
                'path'                => '/campaigns/events',
                'controller'          => MailVotech\CampaignBundle\Controller\Api\EventApiController::class,
            ],
            'mailvotech_api_campaigns_events_contact'     => [
                'path'       => '/campaigns/events/contact/{contactId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\EventLogApiController::getContactEventsAction',
                'method'     => 'GET',
            ],
            'mailvotech_api_campaigns_edit_contact_event' => [
                'path'       => '/campaigns/events/{eventId}/contact/{contactId}/edit',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\EventLogApiController::editContactEventAction',
                'method'     => 'PUT',
            ],
            'mailvotech_api_campaigns_batchedit_events'   => [
                'path'       => '/campaigns/events/batch/edit',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\EventLogApiController::editEventsAction',
                'method'     => 'PUT',
            ],
            'mailvotech_api_campaign_contact_events'      => [
                'path'       => '/campaigns/{campaignId}/events/contact/{contactId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\EventLogApiController::getContactEventsAction',
                'method'     => 'GET',
            ],
            'mailvotech_api_campaigngetcontacts'          => [
                'path'       => '/campaigns/{id}/contacts',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::getContactsAction',
            ],
            'mailvotech_api_campaignaddcontact'           => [
                'path'       => '/campaigns/{id}/contact/{leadId}/add',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::addLeadAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_campaignremovecontact'        => [
                'path'       => '/campaigns/{id}/contact/{leadId}/remove',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::removeLeadAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_contact_clone_campaign' => [
                'path'       => '/campaigns/clone/{campaignId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::cloneCampaignAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_export_campaign' => [
                'path'       => '/campaigns/export/{campaignId}',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::exportCampaignAction',
                'method'     => 'GET',
            ],
            'mailvotech_api_import_campaign' => [
                'path'       => '/campaigns/import',
                'controller' => 'MailVotech\CampaignBundle\Controller\Api\CampaignApiController::importCampaignAction',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mailvotech.campaign.menu.index' => [
                'iconClass' => 'ri-megaphone-fill',
                'route'     => 'mailvotech_campaign_index',
                'access'    => 'campaign:campaigns:view',
                'priority'  => 50,
            ],
        ],
    ],

    'categories' => [
        'campaign' => [
            'class' => MailVotech\CampaignBundle\Entity\Campaign::class,
        ],
    ],
    'parameters' => [
        'campaign_time_wait_on_event_false'                                                     => 'PT1H',
        'campaign_use_summary'                                                                  => 0,
        'campaign_by_range'                                                                     => 0,
        'delete_campaign_event_log_in_background'                                               => false,
        'campaign_email_stats_enabled'                                                          => true,
        'peak_interaction_timer_cache_timeout'                                                  => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_CACHE_TIMEOUT,
        'peak_interaction_timer_best_default_hour_start'                                        => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_BEST_HOUR_START,
        'peak_interaction_timer_best_default_hour_end'                                          => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_BEST_HOUR_END,
        'peak_interaction_timer_best_default_days'                                              => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_BEST_DAYS,
        'peak_interaction_timer_fetch_interactions_from'                                        => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_FETCH_INTERACTIONS_FROM,
        'peak_interaction_timer_fetch_limit'                                                    => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_FETCH_LIMIT,
        'peak_interaction_timer_max_optimal_days'                                               => MailVotech\LeadBundle\Services\PeakInteractionTimer::DEFAULT_MAX_OPTIMAL_DAYS,
        'import_campaigns_dir'                                                                  => '%kernel.project_dir%/var/import',
        'campaigns_resume_stuck_records_after'                                                  => '2025-10-01 00:00:00',
        'campaign_republish_behavior'                                                           => MailVotech\CampaignBundle\Enum\RepublishBehavior::COUNT_ALL_TIME->value,
        'campaign_contact_count_cache_ttl'                                                      => 43200, // 12 hours in seconds
        'campaign_event_cache_ttl'                                                              => 600, // seconds
    ],
];
