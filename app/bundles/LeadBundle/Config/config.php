<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_plugin_timeline_index' => [
                'path'         => '/plugin/{integration}/timeline/{page}',
                'controller'   => 'MailVotech\LeadBundle\Controller\TimelineController::pluginIndexAction',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'mailvotech_plugin_timeline_view' => [
                'path'         => '/plugin/{integration}/timeline/view/{leadId}/{page}',
                'controller'   => 'MailVotech\LeadBundle\Controller\TimelineController::pluginViewAction',
                'requirements' => [
                    'integration' => '.+',
                    'leadId'      => '\d+',
                ],
            ],
            'mailvotech_segment_batch_contact_set' => [
                'path'       => '/segments/batch/contact/set',
                'controller' => 'MailVotech\LeadBundle\Controller\BatchSegmentController::setAction',
            ],
            'mailvotech_segment_batch_contact_view' => [
                'path'       => '/segments/batch/contact/view',
                'controller' => 'MailVotech\LeadBundle\Controller\BatchSegmentController::indexAction',
            ],
            'mailvotech_segment_index' => [
                'path'       => '/segments/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\ListController::indexAction',
            ],
            'mailvotech_segment_action' => [
                'path'       => '/segments/{objectAction}/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\ListController::executeAction',
            ],
            'mailvotech_contactfield_index' => [
                'path'       => '/contacts/fields/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\FieldController::indexAction',
            ],
            'mailvotech_contactfield_action' => [
                'path'       => '/contacts/fields/{objectAction}/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\FieldController::executeAction',
            ],
            'mailvotech_contact_index' => [
                'path'       => '/contacts/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\LeadController::indexAction',
            ],
            'mailvotech_contactnote_index' => [
                'path'       => '/contacts/notes/{leadId}/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\NoteController::indexAction',
                'defaults'   => [
                    'leadId' => 0,
                ],
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contactnote_action' => [
                'path'         => '/contacts/notes/{leadId}/{objectAction}/{objectId}',
                'controller'   => 'MailVotech\LeadBundle\Controller\NoteController::executeNoteAction',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contacttimeline_action' => [
                'path'         => '/contacts/timeline/{leadId}/{page}',
                'controller'   => 'MailVotech\LeadBundle\Controller\TimelineController::indexAction',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contact_timeline_export_action' => [
                'path'         => '/contacts/timeline/batchExport/{leadId}',
                'controller'   => 'MailVotech\LeadBundle\Controller\TimelineController::batchExportAction',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contact_auditlog_action' => [
                'path'         => '/contacts/auditlog/{leadId}/{page}',
                'controller'   => 'MailVotech\LeadBundle\Controller\AuditlogController::indexAction',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contact_auditlog_export_action' => [
                'path'         => '/contacts/auditlog/batchExport/{leadId}',
                'controller'   => 'MailVotech\LeadBundle\Controller\AuditlogController::batchExportAction',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'mailvotech_contact_export_action' => [
                'path'         => '/contacts/contact/export/{contactId}',
                'controller'   => 'MailVotech\LeadBundle\Controller\LeadController::contactExportAction',
                'requirements' => [
                    'contactId' => '\d+',
                ],
            ],
            'mailvotech_import_index' => [
                'path'       => '/{object}/import/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\ImportController::indexAction',
            ],
            'mailvotech_import_action' => [
                'path'       => '/{object}/import/{objectAction}/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\ImportController::executeAction',
            ],
            'mailvotech_contact_action' => [
                'path'       => '/contacts/{objectAction}/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\LeadController::executeAction',
            ],
            'mailvotech_company_index' => [
                'path'       => '/companies/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\CompanyController::indexAction',
            ],
            'mailvotech_company_contacts_list' => [
                'path'         => '/company/{objectId}/contacts/{page}',
                'controller'   => 'MailVotech\LeadBundle\Controller\CompanyController::contactsListAction',
                'requirements' => [
                    'objectId' => '\d+',
                ],
            ],
            'mailvotech_company_graph'     => [
                'path'       => '/company/graph/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\CompanyController::graphAction',
            ],
            'mailvotech_company_action' => [
                'path'       => '/companies/{objectAction}/{objectId}',
                'controller' => 'MailVotech\LeadBundle\Controller\CompanyController::executeAction',
            ],
            'mailvotech_company_export_action' => [
                'path'         => '/companies/company/export/{companyId}',
                'controller'   => 'MailVotech\LeadBundle\Controller\CompanyController::companyExportAction',
                'requirements' => [
                    'companyId' => '\d+',
                ],
            ],
            'mailvotech_segment_contacts' => [
                'path'       => '/segment/view/{objectId}/contact/{page}',
                'controller' => 'MailVotech\LeadBundle\Controller\ListController::contactsAction',
            ],
            'mailvotech_contact_stats' => [
                'path'       => '/contacts/view/{objectId}/stats',
                'controller' => 'MailVotech\LeadBundle\Controller\LeadController::contactStatsAction',
            ],
            'mailvotech_contact_export_download' => [
                'path'       => '/contacts/export/download/{fileName}',
                'controller' => 'MailVotech\LeadBundle\Controller\LeadController::downloadExportAction',
            ],
        ],
        'api' => [
            'mailvotech_api_contactsstandard' => [
                'standard_entity' => true,
                'name'            => 'contacts',
                'path'            => '/contacts',
                'controller'      => MailVotech\LeadBundle\Controller\Api\LeadApiController::class,
            ],
            'mailvotech_api_dncaddcontact' => [
                'path'       => '/contacts/{id}/dnc/{channel}/add',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::addDncAction',
                'method'     => 'POST',
                'defaults'   => [
                    'channel' => 'email',
                ],
            ],
            'mailvotech_api_dncremovecontact' => [
                'path'       => '/contacts/{id}/dnc/{channel}/remove',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::removeDncAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_getcontactevents' => [
                'path'       => '/contacts/{id}/activity',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getActivityAction',
            ],
            'mailvotech_api_getcontactsevents' => [
                'path'       => '/contacts/activity',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getAllActivityAction',
            ],
            'mailvotech_api_getcontactnotes' => [
                'path'       => '/contacts/{id}/notes',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getNotesAction',
            ],
            'mailvotech_api_getcontactdevices' => [
                'path'       => '/contacts/{id}/devices',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getDevicesAction',
            ],
            'mailvotech_api_getcontactcampaigns' => [
                'path'       => '/contacts/{id}/campaigns',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getCampaignsAction',
            ],
            'mailvotech_api_getcontactssegments' => [
                'path'       => '/contacts/{id}/segments',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getListsAction',
            ],
            'mailvotech_api_getcontactscompanies' => [
                'path'       => '/contacts/{id}/companies',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getCompaniesAction',
            ],
            'mailvotech_api_utmcreateevent' => [
                'path'       => '/contacts/{id}/utm/add',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::addUtmTagsAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_utmremoveevent' => [
                'path'       => '/contacts/{id}/utm/{utmid}/remove',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::removeUtmTagsAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_getcontactowners' => [
                'path'       => '/contacts/list/owners',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getOwnersAction',
            ],
            'mailvotech_api_getcontactfields' => [
                'path'       => '/contacts/list/fields',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\LeadApiController::getFieldsAction',
            ],
            'mailvotech_api_getcontactsegments' => [
                'path'       => '/contacts/list/segments',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\ListApiController::getListsAction',
            ],
            'mailvotech_api_segmentsstandard' => [
                'standard_entity' => true,
                'name'            => 'lists',
                'path'            => '/segments',
                'controller'      => MailVotech\LeadBundle\Controller\Api\ListApiController::class,
            ],
            'mailvotech_api_segmentaddcontact' => [
                'path'       => '/segments/{id}/contact/{leadId}/add',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\ListApiController::addLeadAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_segmentaddcontacts' => [
                'path'       => '/segments/{id}/contacts/add',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\ListApiController::addLeadsAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_segmentremovecontact' => [
                'path'       => '/segments/{id}/contact/{leadId}/remove',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\ListApiController::removeLeadAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_companiesstandard' => [
                'standard_entity' => true,
                'name'            => 'companies',
                'path'            => '/companies',
                'controller'      => MailVotech\LeadBundle\Controller\Api\CompanyApiController::class,
            ],
            'mailvotech_api_companyaddcontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/add',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\CompanyApiController::addContactAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_companyremovecontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/remove',
                'controller' => 'MailVotech\LeadBundle\Controller\Api\CompanyApiController::removeContactAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_fieldsstandard' => [
                'standard_entity' => true,
                'name'            => 'fields',
                'path'            => '/fields/{object}',
                'controller'      => MailVotech\LeadBundle\Controller\Api\FieldApiController::class,
                'defaults'        => [
                    'object' => 'contact',
                ],
            ],
            'mailvotech_api_notesstandard' => [
                'standard_entity' => true,
                'name'            => 'notes',
                'path'            => '/notes',
                'controller'      => MailVotech\LeadBundle\Controller\Api\NoteApiController::class,
            ],
            'mailvotech_api_devicesstandard' => [
                'standard_entity' => true,
                'name'            => 'devices',
                'path'            => '/devices',
                'controller'      => MailVotech\LeadBundle\Controller\Api\DeviceApiController::class,
            ],
            'mailvotech_api_tagsstandard' => [
                'standard_entity' => true,
                'name'            => 'tags',
                'path'            => '/tags',
                'controller'      => MailVotech\LeadBundle\Controller\Api\TagApiController::class,
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.lead.leads' => [
                    'iconClass' => 'ri-user-6-fill',
                    'access'    => ['lead:leads:viewown', 'lead:leads:viewother'],
                    'route'     => 'mailvotech_contact_index',
                    'priority'  => 80,
                ],
                'mailvotech.companies.menu.index' => [
                    'route'     => 'mailvotech_company_index',
                    'iconClass' => 'ri-building-2-fill',
                    'access'    => ['lead:leads:viewother'],
                    'priority'  => 75,
                ],
                'mailvotech.lead.list.menu.index' => [
                    'iconClass' => 'ri-pie-chart-fill',
                    'access'    => ['lead:lists:viewown', 'lead:lists:viewother'],
                    'route'     => 'mailvotech_segment_index',
                    'priority'  => 70,
                ],
            ],
        ],
        'admin' => [
            'priority' => 50,
            'items'    => [
                'mailvotech.lead.field.menu.index' => [
                    'id'        => 'mailvotech_lead_field',
                    'iconClass' => 'ri-input-field',
                    'route'     => 'mailvotech_contactfield_index',
                    'access'    => 'lead:fields:full',
                    'priority'  => 19,
                ],
            ],
        ],
    ],
    'categories' => [
        'segment' => [
            'class' => MailVotech\LeadBundle\Entity\LeadList::class,
        ],
    ],
    'parameters' => [
        'parallel_import_limit'               => 1,
        'background_import_if_more_rows_than' => 0,
        'contact_api_count_cache_ttl'         => 5, // in seconds, set null to disable.
        'delete_segment_in_background'        => false,
        'segment_api_count_cache_ttl'         => 43200, // 12 hours in seconds
        'contact_columns'                     => [
            '0' => 'name',
            '1' => 'email',
            '2' => 'location',
            '3' => 'stage',
            '4' => 'points',
            '5' => 'last_active',
            '6' => 'id',
        ],
        'company_columns'                     => [
            '0' => 'companyname',
            '1' => 'companyemail',
            '2' => 'companywebsite',
            '3' => 'score',
            '4' => 'leadcount',
            '5' => 'id',
        ],
        MailVotech\LeadBundle\Field\Settings\BackgroundSettings::CREATE_CUSTOM_FIELD_IN_BACKGROUND  => false,
        'company_unique_identifiers_operator'                                                   => Doctrine\DBAL\Query\Expression\CompositeExpression::TYPE_OR,
        'contact_unique_identifiers_operator'                                                   => Doctrine\DBAL\Query\Expression\CompositeExpression::TYPE_OR,
        'segment_rebuild_time_warning'                                                          => 30,
        'segment_build_time_warning'                                                            => 30,
        'contact_export_in_background'                                                          => true,
        'contact_export_notify_admins'                                                          => true,
        'contact_export_dir'                                                                    => '%mailvotech.application_dir%/media/files/temp',
        'contact_export_batch_size'                                                             => 20000,
        'contact_export_limit'                                                                  => 0,
        'contact_allow_multiple_companies'                                                      => true,
        'import_leads_dir'                                                                      => '%kernel.project_dir%/var/import',
        'update_segment_contact_count_in_background'                                            => false,
        'clear_export_files_after_days'                                                         => 7,
        'update_company_mapping_data_in_background'                                             => false,
    ],
];
