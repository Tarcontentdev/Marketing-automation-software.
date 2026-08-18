<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_sms_index' => [
                'path'       => '/sms/{page}',
                'controller' => 'MailVotech\SmsBundle\Controller\SmsController::indexAction',
            ],
            'mailvotech_sms_action' => [
                'path'       => '/sms/{objectAction}/{objectId}',
                'controller' => 'MailVotech\SmsBundle\Controller\SmsController::executeAction',
            ],
            'mailvotech_sms_contacts' => [
                'path'       => '/sms/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\SmsBundle\Controller\SmsController::contactsAction',
            ],
        ],
        'public' => [
            'mailvotech_sms_callback' => [
                'path'       => '/sms/{transport}/callback',
                'controller' => 'MailVotech\SmsBundle\Controller\ReplyController::callbackAction',
            ],
            /* @deprecated as this was Twilio specific */
            'mailvotech_receive_sms' => [
                'path'       => '/sms/receive',
                'controller' => 'MailVotech\SmsBundle\Controller\ReplyController::callbackAction',
                'defaults'   => [
                    'transport' => 'twilio',
                ],
            ],
        ],
        'api' => [
            'mailvotech_api_smsesstandard' => [
                'standard_entity' => true,
                'name'            => 'smses',
                'path'            => '/smses',
                'controller'      => MailVotech\SmsBundle\Controller\Api\SmsApiController::class,
            ],
            'mailvotech_api_smses_send' => [
                'path'       => '/smses/{id}/contact/{contactId}/send',
                'controller' => 'MailVotech\SmsBundle\Controller\Api\SmsApiController::sendAction',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.sms.smses' => [
                    'route'  => 'mailvotech_sms_index',
                    'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent' => 'mailvotech.core.channels',
                    'checks' => [
                        'integration' => [
                            'Twilio' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'categories' => [
        'sms' => null,
    ],
    'parameters' => [
        'sms_enabled'                                                      => false,
        'sms_username'                                                     => null,
        'sms_password'                                                     => null,
        'sms_messaging_service_sid'                                        => null,
        'sms_frequency_number'                                             => 0,
        'sms_frequency_time'                                               => 'DAY',
        'sms_transport'                                                    => 'mailvotech.sms.twilio.transport',
        MailVotech\SmsBundle\Form\Type\ConfigType::SMS_DISABLE_TRACKABLE_URLS  => false,
    ],
];
