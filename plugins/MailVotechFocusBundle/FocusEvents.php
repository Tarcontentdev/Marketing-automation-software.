<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle;

/**
 * Events available for MailVotechFocusBundle.
 */
final class FocusEvents
{
    /**
     * The mailvotech.focus_pre_save event is dispatched right before a focus is persisted.
     *
     * The event listener receives a MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    public const PRE_SAVE = 'mailvotech.focus_pre_save';

    /**
     * The mailvotech.focus_post_save event is dispatched right after a focus is persisted.
     *
     * The event listener receives a MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    public const POST_SAVE = 'mailvotech.focus_post_save';

    /**
     * The mailvotech.focus_pre_delete event is dispatched before a focus is deleted.
     *
     * The event listener receives a MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    public const PRE_DELETE = 'mailvotech.focus_pre_delete';

    /**
     * The mailvotech.focus_post_delete event is dispatched after a focus is deleted.
     *
     * The event listener receives a MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    public const POST_DELETE = 'mailvotech.focus_post_delete';

    /**
     * The mailvotech.focus_token_replacent event is dispatched after a load content.
     *
     * The event listener receives a MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent instance.
     *
     * @var string
     */
    public const TOKEN_REPLACEMENT = 'mailvotech.focus_token_replacement';

    /**
     * The mailvotech.focus.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.focus.on_campaign_trigger_action';

    /**
     * The mailvotech.focus.on_open event is dispatched when an focus is opened.
     *
     * The event listener receives a
     * MailVotechPlugin\MailVotechFocusBundle\Event\FocusOpenEvent instance.
     *
     * @var string
     */
    public const FOCUS_ON_VIEW = 'mailvotech.focus.on_view';
}
