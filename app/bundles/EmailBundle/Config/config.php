<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_email_batch_categories_view' => [
                'path'       => '/emails/batch/categories/view',
                'controller' => 'MailVotech\EmailBundle\Controller\BatchEmailController::indexAction',
            ],
            'mailvotech_email_batch_categories_set' => [
                'path'       => '/emails/batch/categories/set',
                'controller' => 'MailVotech\EmailBundle\Controller\BatchEmailController::execAction',
            ],
            'mailvotech_email_index' => [
                'path'       => '/emails/{page}',
                'controller' => 'MailVotech\EmailBundle\Controller\EmailController::indexAction',
            ],
            'mailvotech_email_graph_stats' => [
                'path'       => '/emails-graph-stats/{objectId}/{isVariant}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\EmailBundle\Controller\EmailGraphStatsController::viewAction',
            ],
            'mailvotech_email_map_stats' => [
                'path'       => '/emails-map-stats/{objectId}/{isVariant}/{dateFrom}/{dateTo}',
                'controller' => 'MailVotech\EmailBundle\Controller\EmailMapStatsController::viewAction',
            ],
            'mailvotech_email_action' => [
                'path'       => '/emails/{objectAction}/{objectId}',
                'controller' => 'MailVotech\EmailBundle\Controller\EmailController::executeAction',
            ],
            'mailvotech_email_contacts' => [
                'path'       => '/emails/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\EmailBundle\Controller\EmailController::contactsAction',
            ],
            'mailvotech_abtest_generate' => [
                'path'       => '/email/abtest/generate/{objectId}',
                'controller' => 'MailVotech\EmailBundle\Controller\ABTestController::generateABTestAction',
            ],
        ],
        'api' => [
            'mailvotech_api_emailstandard' => [
                'standard_entity' => true,
                'name'            => 'emails',
                'path'            => '/emails',
                'controller'      => MailVotech\EmailBundle\Controller\Api\EmailApiController::class,
            ],
            'mailvotech_api_sendemail' => [
                'path'       => '/emails/{id}/send',
                'controller' => 'MailVotech\EmailBundle\Controller\Api\EmailApiController::sendAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_sendcontactemail' => [
                'path'       => '/emails/{id}/contact/{leadId}/send',
                'controller' => 'MailVotech\EmailBundle\Controller\Api\EmailApiController::sendLeadAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_reply' => [
                'path'       => '/emails/reply/{trackingHash}',
                'controller' => 'MailVotech\EmailBundle\Controller\Api\EmailApiController::replyAction',
                'method'     => 'POST',
            ],
        ],
        'public' => [
            'mailvotech_plugin_tracker' => [
                'path'         => '/plugin/{integration}/tracking.gif',
                'controller'   => 'MailVotech\EmailBundle\Controller\PublicController::pluginTrackingGifAction',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'mailvotech_email_tracker' => [
                'path'       => '/email/{idHash}.gif',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::trackingImageAction',
            ],
            'mailvotech_email_webview' => [
                'path'       => '/email/view/{idHash}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::indexAction',
            ],
            'mailvotech_email_unsubscribe' => [
                'path'       => '/email/unsubscribe/{idHash}/{urlEmail}/{secretHash}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::unsubscribeAction',
                'defaults'   => ['urlEmail' => null, 'secretHash' => null],
            ],
            'mailvotech_email_unsubscribe_all' => [
                'path'       => '/email/dnc/{idHash}/{urlEmail}/{secretHash}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::unsubscribeAllAction',
                'defaults'   => ['urlEmail' => null, 'secretHash' => null],
            ],
            'mailvotech_email_resubscribe' => [
                'path'       => '/email/resubscribe/{idHash}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::resubscribeAction',
            ],
            'mailvotech_mailer_transport_callback' => [
                'path'       => '/mailer/callback',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::mailerCallbackAction',
            ],
            'mailvotech_email_preview' => [
                'path'       => '/email/preview/{objectId}/{objectType}',
                'controller' => 'MailVotech\EmailBundle\Controller\PublicController::previewAction',
                'defaults'   => [
                    'objectType'    => null,
                ],
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.email.emails' => [
                    'route'    => 'mailvotech_email_index',
                    'access'   => ['email:emails:viewown', 'email:emails:viewother'],
                    'parent'   => 'mailvotech.core.channels',
                    'priority' => 100,
                ],
            ],
        ],
    ],
    'categories' => [
        'email' => [
            'class' => MailVotech\EmailBundle\Entity\Email::class,
        ],
    ],
    'parameters' => [
        'mailer_from_name'                                                  => 'MailVotech',
        'mailer_from_email'                                                 => 'email@yoursite.com',
        'mailer_reply_to_email'                                             => null,
        'mailer_return_path'                                                => null,
        'mailer_address_length_limit'                                       => 320,
        'mailer_append_tracking_pixel'                                      => true,
        'mailer_convert_embed_images'                                       => false,
        'mailer_custom_headers'                                             => [],
        'mailer_dsn'                                                        => 'smtp://localhost:1025',
        'unsubscribe_text'                                                  => null,
        'webview_text'                                                      => null,
        'unsubscribe_message'                                               => null,
        'resubscribe_message'                                               => null,
        'email_default_preference_center_id'                                => null,
        'email_default_utm_source'                                          => null,
        'email_default_utm_medium'                                          => null,
        'email_default_utm_campaign'                                        => null,
        'email_default_utm_content'                                         => null,
        'monitored_email'                                                   => [
            'general' => [
                'address'         => null,
                'host'            => null,
                'port'            => '993',
                'encryption'      => '/ssl',
                'user'            => null,
                'password'        => null,
                'use_attachments' => false,
            ],
            'EmailBundle_bounces' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_unsubscribes' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
            'EmailBundle_replies' => [
                'address'           => null,
                'host'              => null,
                'port'              => '993',
                'encryption'        => '/ssl',
                'user'              => null,
                'password'          => null,
                'override_settings' => 0,
                'folder'            => null,
            ],
        ],
        'mailer_is_owner'                                                   => false,
        'disable_unsubscribe_link_header'                                   => false,
        'default_signature_text'                                            => null,
        'email_frequency_number'                                            => 0,
        'email_frequency_time'                                              => 'DAY',
        'show_contact_preferences'                                          => false,
        'show_contact_frequency'                                            => false,
        'show_contact_pause_dates'                                          => false,
        'show_contact_preferred_channels'                                   => false,
        'show_contact_categories'                                           => false,
        'show_contact_segments'                                             => false,
        'disable_trackable_urls'                                            => false,
        'email_draft_enabled'                                               => false,
        'theme_email_default'                                               => 'blank',
        'mailer_memory_msg_limit'                                           => 100,
        MailVotech\EmailBundle\Form\Type\ConfigType::MINIFY_EMAIL_HTML          => false,
        'bot_helper_bot_ratio_threshold'                                    => 0.6,
        'bot_helper_time_email_threshold'                                   => 2, // seconds
        'bot_helper_blocked_user_agents'                                    => [
            // Example of real-world user agents used by bots:
            'Googlebot/2.1 (+http://www.google.com/bot.html)',
            'LinkedInBot/1.0 (compatible; Mozilla/5.0; Jakarta Commons-HttpClient/3.1 +http://www.linkedin.com)',
        ],
        'bot_helper_blocked_ip_addresses'                                   => [],
        'smime_signing_enabled'                                             => false,
        'smime_certificates_path'                                           => '%kernel.project_dir%/var/smime_certificates',
    ],
];
