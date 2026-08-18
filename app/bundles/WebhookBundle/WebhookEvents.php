<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle;

/**
 * Events available for MailVotechWebhookBundle.
 */
final class WebhookEvents
{
    /**
     * The mailvotech.webhook_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_PRE_SAVE = 'mailvotech.webhook_pre_save';

    /**
     * The mailvotech.webhook_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_POST_SAVE = 'mailvotech.webhook_post_save';

    /**
     * The mailvotech.webhook_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_PRE_DELETE = 'mailvotech.webhook_pre_delete';

    /**
     * The mailvotech.webhook_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookBundleEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_POST_DELETE = 'mailvotech.webhook_post_delete';

    /**
     * The mailvotech.webhook_kill event is thrown when target is not available.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_KILL = 'mailvotech.webhook_kill';

    /**
     * The mailvotech.webhook_queue_on_add event is thrown as the queue entity is created, before it is persisted to the database.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookQueueEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_QUEUE_ON_ADD = 'mailvotech.webhook_queue_on_add';

    /**
     * The mailvotech.webhook_pre_execute event is thrown right before a webhook URL is executed.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookExecuteEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_PRE_EXECUTE = 'mailvotech.webhook_pre_execute';

    /**
     * The mailvotech.webhook_post_execute event is thrown right after a webhook URL is executed.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookExecuteEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_POST_EXECUTE = 'mailvotech.webhook_post_execute';

    /**
     * The mailvotech.webhook_on_build event is as the webhook form is built.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookBuild instance.
     *
     * @var string
     */
    public const WEBHOOK_ON_BUILD = 'mailvotech.webhook_on_build';

    /**
     * The mailvotech.webhook.campaign_on_trigger event is dispatched from the mailvotech:campaign:trigger command.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignTriggerEvent instance.
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.webhook.campaign_on_trigger_action';

    /**
     * The mailvotech.webhook_on_request event is fired before request is processed.
     *
     * The event listener receives a MailVotech\WebhookBundle\Event\WebhookRequestEvent instance.
     *
     * @var string
     */
    public const WEBHOOK_ON_REQUEST = 'mailvotech.webhook_on_request';
}
