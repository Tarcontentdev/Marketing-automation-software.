<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle;

/**
 * Events available for NotificationBundle.
 */
final class NotificationEvents
{
    /**
     * The mailvotech.notification_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    public const TOKEN_REPLACEMENT = 'mailvotech.notification_token_replacement';

    /**
     * The mailvotech.notification_form_action_send event is thrown when a notification is sent
     * as part of a form action.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\SendingNotificationEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_ON_FORM_ACTION_SEND = 'mailvotech.notification_form_action_send';

    /**
     * The mailvotech.notification_on_send event is thrown when a notification is sent.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\NotificationSendEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_ON_SEND = 'mailvotech.notification_on_send';

    /**
     * The mailvotech.notification_pre_save event is thrown right before a notification is persisted.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_PRE_SAVE = 'mailvotech.notification_pre_save';

    /**
     * The mailvotech.notification_post_save event is thrown right after a notification is persisted.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_POST_SAVE = 'mailvotech.notification_post_save';

    /**
     * The mailvotech.notification_pre_delete event is thrown prior to when a notification is deleted.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_PRE_DELETE = 'mailvotech.notification_pre_delete';

    /**
     * The mailvotech.notification_post_delete event is thrown after a notification is deleted.
     *
     * The event listener receives a
     * MailVotech\NotificationBundle\Event\NotificationEvent instance.
     *
     * @var string
     */
    public const NOTIFICATION_POST_DELETE = 'mailvotech.notification_post_delete';

    /**
     * The mailvotech.notification.on_batch_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_BATCH_ACTION = 'mailvotech.notification.on_batch_trigger_action';

    /**
     * The mailvotech.notification.on_campaign_trigger_condition event is fired when the campaign condition triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_CONDITION = 'mailvotech.notification.on_campaign_trigger_notification';
}
