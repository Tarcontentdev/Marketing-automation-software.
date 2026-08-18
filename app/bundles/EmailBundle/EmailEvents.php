<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle;

/**
 * Events available for EmailBundle.
 */
final class EmailEvents
{
    /**
     * The mailvotech.email_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    public const TOKEN_REPLACEMENT = 'mailvotech.email_token_replacement';

    /**
     * The mailvotech.email_address_token_replacement event is thrown right before a email address token needs replacement.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    public const ON_EMAIL_ADDRESS_TOKEN_REPLACEMENT = 'mailvotech.email_address_token_replacement';

    /**
     * The mailvotech.email_on_open event is dispatched when an email is opened.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailOpenEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_OPEN = 'mailvotech.email_on_open';

    /**
     * The mailvotech.email_on_send event is dispatched when an email is sent.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailSendEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_SEND = 'mailvotech.email_on_send';

    /**
     * The mailvotech.email_pre_send event is dispatched when an email is clicked.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailSendEvent instance.
     *
     * @var string
     */
    public const EMAIL_PRE_SEND = 'mailvotech.email_pre_send';

    /**
     * The mailvotech.email_on_display event is dispatched when an email is viewed via a browser.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailSendEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_DISPLAY = 'mailvotech.email_on_display';

    /**
     * The mailvotech.email_on_build event is dispatched before displaying the email builder form to allow adding of tokens.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_BUILD = 'mailvotech.email_on_build';

    /**
     * The mailvotech.email_on_toggle_publish event is dispatched right before an email is toggle publish.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_TOGGLE_PUBLISH = 'mailvotech.email_on_toggle_publish';

    /**
     * The mailvotech.email_pre_save event is dispatched right before a email is persisted.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_PRE_SAVE = 'mailvotech.email_pre_save';

    /**
     * The mailvotech.email_post_save event is dispatched right after a email is persisted.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_POST_SAVE = 'mailvotech.email_post_save';

    /**
     * The mailvotech.email_pre_delete event is dispatched prior to when a email is deleted.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_PRE_DELETE = 'mailvotech.email_pre_delete';

    /**
     * The mailvotech.email_post_delete event is dispatched after a email is deleted.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_POST_DELETE = 'mailvotech.email_post_delete';

    /**
     * The mailvotech.monitored_email_config event is dispatched during the configuration in order to inject custom folder locations.
     *
     * The event listener receives a MailVotech\CoreBundle\Event\MonitoredEmailEvent instance.
     *
     * @var string
     */
    public const MONITORED_EMAIL_CONFIG = 'mailvotech.monitored_email_config';

    /**
     * The mailvotech.on_email_parse event is dispatched when a monitored email box retrieves messages.
     *
     * The event listener receives a MailVotech\EmailBundle\Event\ParseEmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_PARSE = 'mailvotech.on_email_parse';

    /**
     * The mailvotech.on_email_pre_fetch event is dispatched prior to fetching email through a configured monitored inbox in order to set
     * search criteria for the mail to be fetched.
     *
     * The event listener receives a MailVotech\EmailBundle\Event\ParseEmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_PRE_FETCH = 'mailvotech.on_email_pre_fetch';

    /**
     * The mailvotech.on_email_failed event is dispatched when an email has failed to clear the queue and is about to be deleted
     * in order to give a bundle a chance to do an action based on failed email if required.
     *
     * The event listener receives a MailVotech\EmailBundle\Event\QueueEmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_FAILED = 'mailvotech.on_email_failed';

    /**
     * The mailvotech.on_email_resend event is dispatched when an attempt to resend an email occurs
     * in order to give a bundle a chance to do an action based on failed email if required.
     *
     * The event listener receives a MailVotech\EmailBundle\Event\QueueEmailEvent instance.
     *
     * @var string
     */
    public const EMAIL_RESEND = 'mailvotech.on_email_resend';

    /**
     * The mailvotech.email.on_campaign_batch_action event is dispatched when the campaign action triggers.
     *
     * The event listener receives a MailVotech\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_BATCH_ACTION = 'mailvotech.email.on_campaign_batch_action';

    /**
     * The mailvotech.email.on_campaign_trigger_decision event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_DECISION = 'mailvotech.email.on_campaign_trigger_decision';

    /**
     * The mailvotech.email.on_campaign_trigger_condition event is dispatched when the campaign condition triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_CONDITION = 'mailvotech.email.on_campaign_trigger_condition';

    /**
     * The mailvotech.email_on_reply event is dispatched when an reply came to an email.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailOpenEvent instance.
     *
     * @var string
     */
    public const EMAIL_ON_REPLY = 'mailvotech.email_on_reply';

    /**
     * The mailvotech.email.on_email_validation event is dispatched when an email is validated through the validator.
     *
     * The event listener receives a MailVotech\EmailBundle\Event\EmailValidationEvent
     *
     * @var string
     */
    public const ON_EMAIL_VALIDATION = 'mailvotech.email.on_email_validation';

    /**
     * The mailvotech.email.on_sent_email_to_user event is dispatched when email is sent to user.
     *
     * The event listener receives a
     * MailVotech\PointBundle\Events\TriggerExecutedEvent
     *
     * @var string
     */
    public const ON_SENT_EMAIL_TO_USER = 'mailvotech.email.on_sent_email_to_user';

    /**
     * @deprecated 2.13.0; to be removed in 3.0. Listen to ON_CAMPAIGN_BATCH_ACTION instead.
     *
     * The mailvotech.email.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.email.on_campaign_trigger_action';

    /**
     * The mailvotech.email.on_transport_webhook event is fired when an email transport service sends MailVotech a webhook request.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\TransportWebhookEvent
     *
     * @var string
     */
    public const ON_TRANSPORT_WEBHOOK = 'mailvotech.email.on_transport_webhook';

    /**
     * The mailvotech.email.on_open_rate_winner event is fired when there is a need to determine open rate winner.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_OPEN_RATE_WINNER = 'mailvotech.email.on_open_rate_winner';

    /**
     * The mailvotech.email.on_open_rate_winner event is fired when there is a need to determine clickthrough rate winner.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_CLICKTHROUGH_RATE_WINNER = 'mailvotech.email.on_clickthrough_rate_winner';

    /**
     * The mailvotech.email.on_email_stat_pre_save event is fired before an email stat batch is saved.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailStatEvent
     */
    public const ON_EMAIL_STAT_PRE_SAVE = 'mailvotech.email.on_email_stat_pre_save';

    /**
     * The mailvotech.email.on_email_stat_post_save event is fired after an email stat batch is saved.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailStatEvent
     */
    public const ON_EMAIL_STAT_POST_SAVE = 'mailvotech.email.on_email_stat_post_save';

    /**
     * The mailvotech.email.on_edit_submit event is fired after an email edit is successfully submitted.
     *
     * The event listener receives a
     * MailVotech\EmailBundle\Event\EmailEditSubmitEvent
     */
    public const ON_EMAIL_EDIT_SUBMIT = 'mailvotech.email.on_edit_submit';
}
