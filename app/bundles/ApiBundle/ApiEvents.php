<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle;

final class ApiEvents
{
    /**
     * The mailvotech.client_pre_save event is thrown right before an API client is persisted.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    public const CLIENT_PRE_SAVE = 'mailvotech.client_pre_save';

    /**
     * The mailvotech.client_post_save event is thrown right after an API client is persisted.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    public const CLIENT_POST_SAVE = 'mailvotech.client_post_save';

    /**
     * The mailvotech.client_post_delete event is thrown after an API client is deleted.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ClientEvent instance.
     *
     * @var string
     */
    public const CLIENT_POST_DELETE = 'mailvotech.client_post_delete';

    /**
     * The mailvotech.build_api_route event is thrown to build MailVotech API routes.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\RouteEvent instance.
     *
     * @var string
     */
    public const BUILD_ROUTE = 'mailvotech.build_api_route';

    /**
     * The mailvotech.api_on_entity_pre_save event is thrown after an entity about to be saved via API.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ApiEntityEvent instance.
     *
     * @var string
     */
    public const API_ON_ENTITY_PRE_SAVE = 'mailvotech.api_on_entity_pre_save';

    /**
     * The mailvotech.api_on_entity_post_save event is thrown after an entity is saved via API.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ApiEntityEvent instance.
     *
     * @var string
     */
    public const API_ON_ENTITY_POST_SAVE = 'mailvotech.api_on_entity_post_save';

    /**
     * The mailvotech.api_pre_serialization_context event is dispatched before the serialization context is created for the view.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ApiSerializationContextEvent instance.
     *
     * @var string
     */
    public const API_PRE_SERIALIZATION_CONTEXT = 'mailvotech.api_pre_serialization_context';

    /**
     * The mailvotech.api_post_serialization_context event is dispatched after the serialization context is created for the view.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ApiSerializationContextEvent instance.
     *
     * @var string
     */
    public const API_POST_SERIALIZATION_CONTEXT = 'mailvotech.api_post_serialization_context';

    /**
     * The mailvotech.api_platform_permission_context event is dispatched before API Platform permission checks are evaluated.
     *
     * The event listener receives a MailVotech\ApiBundle\Event\ApiPlatformPermissionContextEvent instance.
     *
     * @var string
     */
    public const API_PLATFORM_PERMISSION_CONTEXT = 'mailvotech.api_platform_permission_context';
}
