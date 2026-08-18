<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle;

/**
 * Events available for MailVotechSocialBundle.
 */
final class SocialEvents
{
    /**
     * The mailvotech.monitor_pre_save event is dispatched right before a monitor is persisted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const MONITOR_PRE_SAVE = 'mailvotech.monitor_pre_save';

    /**
     * The mailvotech.monitor_post_save event is dispatched right after a monitor is persisted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const MONITOR_POST_SAVE = 'mailvotech.monitor_post_save';

    /**
     * The mailvotech.monitor_pre_delete event is dispatched before a monitor item is deleted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const MONITOR_PRE_DELETE = 'mailvotech.monitor_pre_delete';

    /**
     * The mailvotech.monitor_post_delete event is dispatched after a monitor is deleted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const MONITOR_POST_DELETE = 'mailvotech.monitor_post_delete';

    /**
     * The mailvotech.monitor_post_process event is dispatched after a monitor is processed passing along the data gleaned.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const MONITOR_POST_PROCESS = 'mailvotech.monitor_post_process';

    /**
     * The mailvotech.tweet_pre_save event is dispatched right before a tweet is persisted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const TWEET_PRE_SAVE = 'mailvotech.tweet_pre_save';

    /**
     * The mailvotech.tweet_post_save event is dispatched right after a tweet is persisted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const TWEET_POST_SAVE = 'mailvotech.tweet_post_save';

    /**
     * The mailvotech.tweet_pre_delete event is dispatched before a tweet item is deleted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const TWEET_PRE_DELETE = 'mailvotech.tweet_pre_delete';

    /**
     * The mailvotech.tweet_post_delete event is dispatched after a tweet is deleted.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechSocialBundle\Event\SocialEvent instance.
     *
     * @var string
     */
    public const TWEET_POST_DELETE = 'mailvotech.tweet_post_delete';

    /**
     * The mailvotech.social.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.social.on_campaign_trigger_action';
}
