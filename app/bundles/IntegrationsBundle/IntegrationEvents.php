<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle;

final class IntegrationEvents
{
    /**
     * The mailvotech.integration.sync_post_execute_integration event is dispatched after a sync is executed.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\SyncEvent object.
     *
     * @var string
     */
    public const INTEGRATION_POST_EXECUTE = 'mailvotech.integration.sync_post_execute_integration';

    /**
     * The mailvotech.integration.config_form_loaded event is dispatched when config page for integration is loaded.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\FormLoadEvent object.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_FORM_LOAD = 'mailvotech.integration.config_form_loaded';

    /**
     * The mailvotech.integration.config_before_save event is dispatched prior to an integration's configuration is saved.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\ConfigSaveEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_BEFORE_SAVE = 'mailvotech.integration.config_before_save';

    /**
     * The mailvotech.integration.config_after_save event is dispatched after an integration's configuration is saved.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\ConfigSaveEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_AFTER_SAVE = 'mailvotech.integration.config_after_save';

    /**
     * The mailvotech.integration.config_before_save event is dispatched prior to an integration's configuration is saved.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\ConfigAuthUrlEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CONFIG_ON_GENERATE_AUTH_URL = 'mailvotech.integration.INTEGRATION_CONFIG_ON_GENERATE_AUTH_URL';

    /**
     * The mailvotech.integration.keys_before_encryption event is dispatched prior to encrypting keys to be stored into the database.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\KeysEncryptionEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_KEYS_BEFORE_ENCRYPTION = 'mailvotech.integration.keys_before_encryption';

    /**
     * The mailvotech.integration.keys_after_decryption event is dispatched after fetching and decrypting keys from the database.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\KeysDecryptionEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_KEYS_AFTER_DECRYPTION = 'mailvotech.integration.keys_after_decryption';

    /**
     * The mailvotech.integration.mailvotech_sync_field_load event is dispatched when MailVotech sync fields are build.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\MailVotechSyncFieldsLoadEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_MAILVOTECH_SYNC_FIELDS_LOAD = 'mailvotech.integration.mailvotech_sync_field_load';

    /**
     * The mailvotech.integration.INTEGRATION_COLLECT_INTERNAL_OBJECTS event is dispatched when a list of MailVotech internal objects is build.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_COLLECT_INTERNAL_OBJECTS = 'mailvotech.integration.INTEGRATION_COLLECT_INTERNAL_OBJECTS';

    /**
     * The mailvotech.integration.INTEGRATION_CREATE_INTERNAL_OBJECTS event is dispatched when a list of MailVotech internal objects should be created.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectCreateEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_CREATE_INTERNAL_OBJECTS = 'mailvotech.integration.INTEGRATION_CREATE_INTERNAL_OBJECTS';

    /**
     * The mailvotech.integration.INTEGRATION_UPDATE_INTERNAL_OBJECTS event is dispatched when a list of MailVotech internal objects should be updated.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectUpdateEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_UPDATE_INTERNAL_OBJECTS = 'mailvotech.integration.INTEGRATION_UPDATE_INTERNAL_OBJECTS';

    /**
     * The mailvotech.integration.INTEGRATION_FIND_INTERNAL_RECORDS event is dispatched when a list of MailVotech internal object records by ID is requested.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectFindEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_FIND_INTERNAL_RECORDS = 'mailvotech.integration.INTEGRATION_FIND_INTERNAL_RECORDS';

    /**
     * The mailvotech.integration.INTEGRATION_FIND_OWNER_IDS event is dispatched when a list of MailVotech internal owner IDs by internal object ID is requested.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectFindEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_FIND_OWNER_IDS = 'mailvotech.integration.INTEGRATION_FIND_OWNER_IDS';

    /**
     * The mailvotech.integration.INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE event is dispatched when a MailVotech internal object route is requested.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectOwnerEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE = 'mailvotech.integration.INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE';

    /**
     * This event is dispatched when a tokens are being built to represent links to mapped integration objects.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\MappedIntegrationObjectTokenEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_OBJECT_TOKEN_EVENT = 'mailvotech.integration.INTEGRATION_OBJECT_TOKEN_EVENT';

    /**
     * This event is dispatched when a MailVotech contact field changes are about to be stored to the sync_object_field_change_report table.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalContactEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BEFORE_CONTACT_FIELD_CHANGES = 'mailvotech.integration.INTEGRATION_BEFORE_CONTACT_FIELD_CHANGES';

    /**
     * This event is dispatched when a MailVotech company field changes are about to be stored to the sync_object_field_change_report table.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalCompanyEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BEFORE_COMPANY_FIELD_CHANGES = 'mailvotech.integration.INTEGRATION_BEFORE_COMPANY_FIELD_CHANGES';

    /**
     * The mailvotech.integration.INTEGRATION_FIND_INTERNAL_RECORD event is dispatched when a list of MailVotech internal object record by ID is requested.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalObjectFindByIdEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_FIND_INTERNAL_RECORD = 'mailvotech.integration.INTEGRATION_FIND_INTERNAL_RECORD';

    /**
     * This event is dispatched when a MailVotech contact field changes are about to be used in full object report builder.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalContactEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BEFORE_FULL_CONTACT_REPORT_BUILD = 'mailvotech.integration.INTEGRATION_BEFORE_FULL_CONTACT_REPORT_BUILD';

    /**
     * This event is dispatched when a MailVotech company field changes are about to be used in full object report builder.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\InternalCompanyEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BEFORE_FULL_COMPANY_REPORT_BUILD = 'mailvotech.integration.INTEGRATION_BEFORE_FULL_COMPANY_REPORT_BUILD';

    /**
     * This event is dispatched when a batch of objects have synced from an integration to MailVotech after the sync engine has processed everything
     * so that listeners can then act on mappings stored in the sync_object_mapping table.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\CompletedSyncIterationEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BATCH_SYNC_COMPLETED_INTEGRATION_TO_MAILVOTECH = 'mailvotech.integration.INTEGRATION_BATCH_SYNC_COMPLETED_INTEGRATION_TO_MAILVOTECH';

    /**
     * This event is dispatched when a batch of objects have synced from MailVotech to the integration after the sync engine has processed everything
     * so that listeners can then act on mappings stored in the sync_object_mapping table.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\CompletedSyncIterationEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_BATCH_SYNC_COMPLETED_MAILVOTECH_TO_INTEGRATION = 'mailvotech.integration.INTEGRATION_BATCH_SYNC_COMPLETED_MAILVOTECH_TO_INTEGRATION';

    /**
     * This event is dispatched when api keys is updated/inserted.
     *
     * The event listener receives a MailVotech\IntegrationsBundle\Event\KeysSaveEvent instance.
     *
     * @var string
     */
    public const INTEGRATION_API_KEYS_BEFORE_SAVE = 'mailvotech.integration.INTEGRATION_API_KEYS_BEFORE_SAVE';
}
