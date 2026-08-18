<?php

declare(strict_types=1);

use MailVotech\FormBundle\Helper\BlockedFreeEmailProvidersHelper;

return [
    'routes' => [
        'main' => [
            'mailvotech_formaction_action' => [
                'path'       => '/forms/action/{objectAction}/{objectId}',
                'controller' => 'MailVotech\FormBundle\Controller\ActionController::executeAction',
            ],
            'mailvotech_formfield_action' => [
                'path'       => '/forms/field/{objectAction}/{objectId}',
                'controller' => 'MailVotech\FormBundle\Controller\FieldController::executeAction',
            ],
            'mailvotech_form_index' => [
                'path'       => '/forms/{page}',
                'controller' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
            ],
            'mailvotech_form_results' => [
                'path'       => '/forms/results/{objectId}/{page}',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::indexAction',
            ],
            'mailvotech_form_export' => [
                'path'       => '/forms/results/{objectId}/export/{format}',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::exportAction',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'mailvotech_form_results_add_segment' => [
                'path'       => '/forms/results/{objectId}/add-to-segment',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::addToSegmentAction',
            ],
            'mailvotech_form_results_action' => [
                'path'       => '/forms/results/{formId}/{objectAction}/{objectId}',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::executeAction',
                'defaults'   => [
                    'objectId' => 0,
                ],
            ],
            'mailvotech_form_action' => [
                'path'       => '/forms/{objectAction}/{objectId}',
                'controller' => 'MailVotech\FormBundle\Controller\FormController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_formstandard' => [
                'standard_entity' => true,
                'name'            => 'forms',
                'path'            => '/forms',
                'controller'      => MailVotech\FormBundle\Controller\Api\FormApiController::class,
            ],
            'mailvotech_api_formresults' => [
                'path'       => '/forms/{formId}/submissions',
                'controller' => 'MailVotech\FormBundle\Controller\Api\SubmissionApiController::getEntitiesAction',
            ],
            'mailvotech_api_formresult' => [
                'path'       => '/forms/{formId}/submissions/{submissionId}',
                'controller' => 'MailVotech\FormBundle\Controller\Api\SubmissionApiController::getEntityAction',
            ],
            'mailvotech_api_contactformresults' => [
                'path'       => '/forms/{formId}/submissions/contact/{contactId}',
                'controller' => 'MailVotech\FormBundle\Controller\Api\SubmissionApiController::getEntitiesForContactAction',
            ],
            'mailvotech_api_formdeletefields' => [
                'path'       => '/forms/{formId}/fields/delete',
                'controller' => 'MailVotech\FormBundle\Controller\Api\FormApiController::deleteFieldsAction',
                'method'     => 'DELETE',
            ],
            'mailvotech_api_formdeleteactions' => [
                'path'       => '/forms/{formId}/actions/delete',
                'controller' => 'MailVotech\FormBundle\Controller\Api\FormApiController::deleteActionsAction',
                'method'     => 'DELETE',
            ],
        ],
        'public' => [
            'mailvotech_form_file_download' => [
                'path'       => '/forms/results/file/{submissionId}/{field}',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::downloadFileAction',
            ],
            'mailvotech_form_file_download_by_name' => [
                'path'       => '/forms/results/file/{fieldId}/filename/{fileName}',
                'controller' => 'MailVotech\FormBundle\Controller\ResultController::downloadFileByFileNameAction',
            ],
            'mailvotech_form_postresults' => [
                'path'       => '/form/submit',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::submitAction',
            ],
            'mailvotech_form_generateform' => [
                'path'       => '/form/generate.js',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::generateAction',
            ],
            'mailvotech_form_postmessage' => [
                'path'       => '/form/message',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::messageAction',
            ],
            'mailvotech_form_preview' => [
                'path'       => '/form/{id}',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::previewAction',
                'defaults'   => [
                    'id' => '0',
                ],
            ],
            'mailvotech_form_embed' => [
                'path'       => '/form/embed/{id}',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::embedAction',
            ],
            'mailvotech_form_postresults_ajax' => [
                'path'       => '/form/submit/ajax',
                'controller' => 'MailVotech\FormBundle\Controller\AjaxController::submitAction',
            ],
            'mailvotech_form_company_lookup' => [
                'path'       => '/form/company-lookup/autocomplete',
                'controller' => 'MailVotech\FormBundle\Controller\PublicController::lookupCompanyAction',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.form.forms' => [
                    'route'    => 'mailvotech_form_index',
                    'access'   => ['form:forms:viewown', 'form:forms:viewother'],
                    'parent'   => 'mailvotech.core.components',
                    'priority' => 200,
                ],
            ],
        ],
    ],

    'categories' => [
        'form' => [
            'class' => MailVotech\FormBundle\Entity\Form::class,
        ],
    ],

    'parameters' => [
        'form_upload_dir'              => '%mailvotech.application_dir%/media/files/form',
        'blacklisted_extensions'       => ['php', 'sh'],
        'do_not_submit_emails'         => [],
        'blocked_free_email_providers' => BlockedFreeEmailProvidersHelper::load(),
        'form_results_data_sources'    => false,
        'successful_submit_action'     => 'top',
        'form_field_autofill'          => false,
    ],
];
