<?php

declare(strict_types=1);

namespace MailVotech\PageBundle;

/**
 * Events available for PageBundle.
 */
final class PageEvents
{
    /**
     * The mailvotech.video_on_hit event is thrown when a public page is browsed and a hit recorded in the analytics table.
     *
     * The event listener receives a MailVotech\PageBundle\Event\VideoHitEvent instance.
     *
     * @var string
     */
    public const VIDEO_ON_HIT = 'mailvotech.video_on_hit';

    /**
     * The mailvotech.page_on_hit event is thrown when a public page is browsed and a hit recorded in the analytics table.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageHitEvent instance.
     *
     * @var string
     */
    public const PAGE_ON_HIT = 'mailvotech.page_on_hit';

    /**
     * The mailvotech.page_on_build event is thrown before displaying the page builder form to allow adding of tokens.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_ON_BUILD = 'mailvotech.page_on_build';

    /**
     * The mailvotech.page_on_display event is thrown before displaying the page content.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageDisplayEvent instance.
     *
     * @var string
     */
    public const PAGE_ON_DISPLAY = 'mailvotech.page_on_display';

    /**
     * The mailvotech.page_on_toggle_publish event is dispatched right before a page is toggle publish.
     *
     * The event listener receives a
     * MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_ON_TOGGLE_PUBLISH = 'mailvotech.page_on_toggle_publish';

    /**
     * The mailvotech.page_pre_save event is thrown right before a page is persisted.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_PRE_SAVE = 'mailvotech.page_pre_save';

    /**
     * The mailvotech.page_post_save event is thrown right after a page is persisted.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_POST_SAVE = 'mailvotech.page_post_save';

    /**
     * The mailvotech.page_pre_delete event is thrown prior to when a page is deleted.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_PRE_DELETE = 'mailvotech.page_pre_delete';

    /**
     * The mailvotech.page_post_delete event is thrown after a page is deleted.
     *
     * The event listener receives a MailVotech\PageBundle\Event\PageEvent instance.
     *
     * @var string
     */
    public const PAGE_POST_DELETE = 'mailvotech.page_post_delete';

    /**
     * The mailvotech.redirect_do_not_track event is thrown when converting email links to trackables/redirectables in order to compile of list of tokens/URLs
     * to ignore.
     *
     * The event listener receives a MailVotech\PageBundle\Event\UntrackableUrlsEvent instance.
     *
     * @var string
     */
    public const REDIRECT_DO_NOT_TRACK = 'mailvotech.redirect_do_not_track';

    /**
     * The mailvotech.page.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_DECISION = 'mailvotech.page.on_campaign_trigger_decision';

    /**
     * The mailvotech.page.on_campaign_trigger_action event is fired when the campaign action fired.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.page.on_campaign_trigger_action';

    /**
     * The mailvotech.page.on_redirect_generate event is fired when generating a redirect.
     *
     * The event listener receives a
     * MailVotech\PageBundle\Event\RedirectGenerationEvent
     */
    public const ON_REDIRECT_GENERATE = 'mailvotech.page.on_redirect_generate';

    /**
     * The mailvotech.page.on_bounce_rate_winner event is fired when there is a need to determine bounce rate winner.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_BOUNCE_RATE_WINNER = 'mailvotech.page.on_bounce_rate_winner';

    /**
     * The mailvotech.page.on_dwell_time_winner event is fired when there is a need to determine a winner based on dwell time.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_DWELL_TIME_WINNER = 'mailvotech.page.on_dwell_time_winner';

    /**
     * The mailvotech.page.on_contact_tracked event is dispatched when a contact is tracked via the mt() tracking event.
     *
     * The event listener receives a
     * MailVotech\PageBundle\Event\TrackingEvent
     */
    public const ON_CONTACT_TRACKED = 'mailvotech.page.on_contact_tracked';
}
