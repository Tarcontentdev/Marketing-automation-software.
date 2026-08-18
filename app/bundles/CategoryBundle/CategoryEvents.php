<?php

declare(strict_types=1);

namespace MailVotech\CategoryBundle;

/**
 * Events available for CategoryBundle.
 */
final class CategoryEvents
{
    /**
     * The mailvotech.category_pre_save event is thrown right before a category is persisted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_PRE_SAVE = 'mailvotech.category_pre_save';

    /**
     * The mailvotech.category_post_save event is thrown right after a category is persisted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_POST_SAVE = 'mailvotech.category_post_save';

    /**
     * The mailvotech.category_pre_delete event is thrown prior to when a category is deleted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_PRE_DELETE = 'mailvotech.category_pre_delete';

    /**
     * The mailvotech.category_post_delete event is thrown after a category is deleted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_POST_DELETE = 'mailvotech.category_post_delete';

    /**
     * The mailvotech.category_on_bundle_list_build event is thrown when a list of bundles supporting categories is build.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryTypesEvent instance.
     *
     * @var string
     */
    public const CATEGORY_ON_BUNDLE_LIST_BUILD = 'mailvotech.category_on_bundle_list_build';
}
