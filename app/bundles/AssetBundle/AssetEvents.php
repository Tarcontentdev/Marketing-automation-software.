<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle;

/**
 * Events available for AssetBundle.
 */
final class AssetEvents
{
    /**
     * The mailvotech.asset_on_load event is dispatched when a public asset is downloaded, publicly viewed, or redirected to (remote).
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetLoadEvent instance.
     *
     * @var string
     */
    public const ASSET_ON_LOAD = 'mailvotech.asset_on_load';

    /**
     * The mailvotech.asset_on_remote_browse event is dispatched when browsing a remote provider.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\RemoteAssetBrowseEvent instance.
     *
     * @var string
     */
    public const ASSET_ON_REMOTE_BROWSE = 'mailvotech.asset_on_remote_browse';

    /**
     * The mailvotech.asset_on_upload event is dispatched before uploading a file.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    public const ASSET_ON_UPLOAD = 'mailvotech.asset_on_upload';

    /**
     * The mailvotech.asset_pre_save event is dispatched right before a asset is persisted.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    public const ASSET_PRE_SAVE = 'mailvotech.asset_pre_save';

    /**
     * The mailvotech.asset_post_save event is dispatched right after a asset is persisted.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    public const ASSET_POST_SAVE = 'mailvotech.asset_post_save';

    /**
     * The mailvotech.asset_pre_delete event is dispatched prior to when a asset is deleted.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    public const ASSET_PRE_DELETE = 'mailvotech.asset_pre_delete';

    /**
     * The mailvotech.asset_post_delete event is dispatched after a asset is deleted.
     *
     * The event listener receives a
     * MailVotech\AssetBundle\Event\AssetEvent instance.
     *
     * @var string
     */
    public const ASSET_POST_DELETE = 'mailvotech.asset_post_delete';

    /**
     * The mailvotech.asset.on_campaign_trigger_decision event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_DECISION = 'mailvotech.asset.on_campaign_trigger_decision';

    /**
     * The mailvotech.asset.on_download_rate_winner event is fired when there is a need to determine download rate winner.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_DOWNLOAD_RATE_WINNER = 'mailvotech.asset.on_download_rate_winner';
}
