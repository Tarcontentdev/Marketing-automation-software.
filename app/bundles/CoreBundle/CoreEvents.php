<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle;

final class CoreEvents
{
    /**
     * The mailvotech.build_menu event is thrown to render menu items.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\MenuEvent instance.
     *
     * @var string
     */
    public const BUILD_MENU = 'mailvotech.build_menu';

    /**
     * The mailvotech.build_route event is thrown to build MailVotech bundle routes.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\RouteEvent instance.
     *
     * @var string
     */
    public const BUILD_ROUTE = 'mailvotech.build_route';

    /**
     * The mailvotech.global_search event is thrown to build global search results from applicable bundles.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\GlobalSearchEvent instance.
     *
     * @var string
     */
    public const GLOBAL_SEARCH = 'mailvotech.global_search';

    /**
     * The mailvotech.list_stats event is thrown to build statistical results from applicable bundles/database tables.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\StatsEvent instance.
     *
     * @var string
     */
    public const LIST_STATS = 'mailvotech.list_stats';

    /**
     * The mailvotech.build_command_list event is thrown to build global search's autocomplete list.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\CommandListEvent instance.
     *
     * @var string
     */
    public const BUILD_COMMAND_LIST = 'mailvotech.build_command_list';

    /**
     * The mailvotech.on_fetch_icons event is thrown to fetch icons of menu items.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\IconEvent instance.
     *
     * @var string
     */
    public const FETCH_ICONS = 'mailvotech.on_fetch_icons';

    /**
     * The mailvotech.pre_upgrade is dispatched before an upgrade.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\UpgradeEvent instance.
     *
     * @var string
     */
    public const PRE_UPGRADE = 'mailvotech.pre_upgrade';

    /**
     * The mailvotech.post_upgrade is dispatched after an upgrade.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\UpgradeEvent instance.
     *
     * @var string
     */
    public const POST_UPGRADE = 'mailvotech.post_upgrade';

    /**
     * The mailvotech.build_embeddable_js event is dispatched to allow plugins to extend the mailvotech tracking js.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\BuildJsEvent instance.
     *
     * @var string
     */
    public const BUILD_MAILVOTECH_JS = 'mailvotech.build_embeddable_js';

    /**
     * The mailvotech.maintenance_cleanup_data event is dispatched to purge old data.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\MaintenanceEvent instance.
     *
     * @var string
     */
    public const MAINTENANCE_CLEANUP_DATA = 'mailvotech.maintenance_cleanup_data';

    /**
     * The mailvotech.view_inject_custom_buttons event is dispatched to inject custom buttons into MailVotech's UI by plugins/other bundles.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\CustomButtonEvent instance.
     *
     * @var string
     */
    public const VIEW_INJECT_CUSTOM_BUTTONS = 'mailvotech.view_inject_custom_buttons';

    /**
     * The mailvotech.view_inject_custom_content event is dispatched by views to collect custom content to be injected in UIs.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\CustomContentEvent instance.
     *
     * @var string
     */
    public const VIEW_INJECT_CUSTOM_CONTENT = 'mailvotech.view_inject_custom_content';

    /**
     * The mailvotech.view_inject_custom_template event is dispatched when a template is to be rendered giving opportunity to change template or
     * vars.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\CustomTemplateEvent instance.
     *
     * @var string
     */
    public const VIEW_INJECT_CUSTOM_TEMPLATE = 'mailvotech.view_inject_custom_template';

    /**
     * The mailvotech.view_inject_custom_assets event is dispatched when assets are rendered.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\CustomAssetsEvent instance.
     *
     * @var string
     */
    public const VIEW_INJECT_CUSTOM_ASSETS = 'mailvotech.view_inject_custom_assets';

    /**
     * The mailvotech.on_generated_columns_build event is dispatched when a list of generated columns is being built.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\GeneratedColumnsEvent instance.
     *
     * @var string
     */
    public const ON_GENERATED_COLUMNS_BUILD = 'mailvotech.on_generated_columns_build';
}
